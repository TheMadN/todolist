@extends('layouts.app')
@section('title')
    My Discord DM App
@endsection
@section('content')

    <div class="row mt-3">
        <div class="col-12 align-self-center">
            <!-- <ul class="list-group">
                @foreach($todos as $todo)
                    <li class="list-group-item"><a href="details/{{$todo->id}}" style="color: cornflowerblue">{{$todo->name}}</a></li>
                @endforeach
            </ul> -->
            <form method="post" action="/sendDiscord" enctype="multipart/form-data">
                @csrf
                <!-- <input type="hidden" name="todos" value="{{ json_encode($todos) }}"> -->
                <div class="form-group">
                    <label for="user_id">User ID's:</label>
                    <input type="text" name="user_id" id="user_id" class="form-control">
                </div>
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" name="title" id="title" class="form-control">
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea name="message" id="message" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label for="attachments">Attachments:</label>
                    <input type="file" name="attachments[]" id="attachments" class="form-control-file" multiple>
                </div>
                <div class="form-group">
                    <label for="attachment_description">Attachment description:</label>
                    <input type="text" name="attachment_description" id="attachment_description" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Send to Discord</button>
            </form>
        </div>
    </div>

@endsection
