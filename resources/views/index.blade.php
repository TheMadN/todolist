@extends('layouts.app')
@section('title')
    My Todo App
@endsection
@section('content')

    <div class="row mt-3">
        <div class="col-12 align-self-center">
            <ul class="list-group">
                @foreach($todos as $todo)
                    <li class="list-group-item"><a href="details/{{$todo->id}}" style="color: cornflowerblue">{{$todo->name}}</a></li>
                @endforeach
            </ul>
            <form method="post" action="/send" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="todos" value="{{ json_encode($todos) }}">
                <div class="form-group">
                    <label for="attachments">Attachments:</label>
                    <input type="file" name="attachments[]" id="attachments" class="form-control-file" multiple>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Send to Webhook</button>
            </form>
        </div>
    </div>

@endsection
