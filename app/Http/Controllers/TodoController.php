<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;

use todo\resources\img;

use App\Models\Todo;

class TodoController extends Controller
{
    public function index()
    {
        $todo = Todo::all();
        return view('index')->with('todos', $todo);
    }
    public function create()
    {
        return view('create');
    }
    public function details(Todo $todo)
    {

        return view('details')->with('todos', $todo);
    }

    public function edit(Todo $todo)
    {

        return view('edit')->with('todos', $todo);;
    }
    public function update(Todo $todo)
    {

        try {
            $this->validate(request(), [
                'name' => ['required'],
                'description' => ['required'],

            ]);
        } catch (ValidationException $e) {
        }

        $data = request()->all();


        $todo->name = $data['name'];
        $todo->description = $data['description'];
        $todo->save();

        session()->flash('success', 'Todo updated successfully');

        return redirect('/');
    }
    public function delete(Todo $todo)
    {

        $todo->delete();

        return redirect('/');
    }

    public function store()
    {
        try {
            $this->validate(request(), [
                'name' => ['required'],
                'description' => ['required']
            ]);
        } catch (ValidationException $e) {
        }

        $data = request()->all();

        $todo = new Todo();
        //On the left is the field name in DB and on the right is field name in Form/view
        $todo->name = $data['name'];
        $todo->description = $data['description'];
        $todo->save();
        session()->flash('success', 'Todo created succesfully');
        return redirect('/');
    }

    public function sendZapier(Request $request)
    {
        try {
            $this->validate($request, [
                'todos' => ['required'],
                'attachments.*' => ['file', 'max:10240'], // Validate attachments, max size 10MB per file
            ]);

            // Retrieve todos from request
            $todos = json_decode($request->input('todos'));

            $webhookUrl = 'https://hooks.zapier.com/hooks/catch/18937532/3vjrmlp/'; // Replace with your Zapier webhook URL

            // Encode the image as base64
            $imagePath = public_path('windeshiemLogo.png'); // Replace 'your_image.jpg' with your image file name and path
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/jpeg;base64,' . $imageData;

            // Example rich text message with inline image
            $message = '<b>Hello</b>, this is a <i>test</i> <u>message</u> from PHP!<br>';
            $message .= '<img src="' . $imageSrc . '" alt="Your Image">';

            $data = array(
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s'),
                'todos' => $todos
            );

            // Initialize cURL session
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $webhookUrl);
            curl_setopt($ch, CURLOPT_POST, 1);

            // Prepare the payload for multipart/form-data
            $payload = array(
                'data' => json_encode($data)
            );

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $key => $file) {
                    $payload["attachment[$key]"] = curl_file_create($file->getPathname(), $file->getMimeType(), $file->getClientOriginalName());
                }
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute cURL request
            $result = curl_exec($ch);

            // Check for errors
            if (curl_errno($ch)) {
                throw new \Exception('Error sending data to Zapier: ' . curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            // Decode response
            $response = json_decode($result, true);
            print_r($response);

            session()->flash('success', 'Todos sent to webhook successfully.');
        } catch (ValidationException $e) {
            // Handle other exceptions
            session()->flash('error', $e->getMessage());
        }

        return redirect('/');
    }

    public function sendDiscord(Request $request)
    {
        try {
            $this->validate($request, [
                'user_id' => ['required'],
                'title' => ['required'],
                'message' => ['required'],
                'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif'], // Validate attachments, max size 10MB per file, allowed MIME types
                'attachment_descriptions.*' => ['required'] // Validation rule for image descriptions
            ]);

            // Retrieve discord token from .env file
            $discordToken = env('DISCORD_TOKEN');

            // Retrieve data from the form
            $userIds = explode(', ', $request->input('user_id'));
            $title = $request->input('title');
            $message = $request->input('message');
            $attachmentDescriptions = $request->input('attachment_descriptions');

            // Prepare message payload
            $payload = [
                'embeds' => [
                    [
                        'title' => $title,
                        'description' => $message,
                        'color' => 0x800080, // Example color (purple)
                    ]
                ]
            ];

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $key => $attachment) {
                    // Validate file type again (just to be safe)
                    $extension = $attachment->getClientOriginalExtension();
                    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        throw new \Exception('Unsupported file type. Please provide images of type jpg, jpeg, png, webp, or gif.');
                    }

                    // Add image embed
                    $payload['embeds'][] = [
                        'title' => $attachmentDescriptions[$key], // Use image description as the title of the embed
                        'image' => [
                            'url' => asset($attachment->store('attachments')) // Store the image and use its URL in the embed
                        ],
                        'color' => 0x800080 // Example color (purple)
                    ];
                }
            }

            // Loop through user IDs and send messages to each user
            foreach ($userIds as $userId) {
                // Replace 'YOUR_DISCORD_TOKEN' with your actual Discord bot token
                $response = Http::withToken($discordToken)->post("https://discord.com/api/v10/users/@me/channels", [
                    'recipient_id' => $userId,
                ]);

                $channelId = $response->json()['id'];

                Http::withToken($discordToken)->post("https://discord.com/api/v10/channels/$channelId/messages", $payload);
            }

            // Success response
            session()->flash('success', 'Todos sent to webhook successfully.');
        } catch (\Exception $e) {
            // Handle other exceptions
            session()->flash('error', $e->getMessage());
        }
        dd();
        return redirect('/');
    }
}
