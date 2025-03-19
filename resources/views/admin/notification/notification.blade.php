@extends('layouts.contentLayoutMaster')
@section('title','Notification')
@section('content')
    <section id="description" class="card">
        <div class="card-header">
            <h4 class="card-title"></h4>
        </div>
        <div class="card-content">
            <div class="card-body">
                <form action="{{route('admin.Notification.store')}}" method="post">
                    @csrf
                    @php
                        $form = new Mmeshkatian\Ariel\FormBuilder();
                        $form->addField('title','Title')->setType('text');
                        $form->addField('msg','Text')->setType('textarea');
                        echo $form->render();
                    @endphp
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </section>
@endsection
@section('myscript')
    <script src="http://cdn.ckeditor.com/4.13.1/full/ckeditor.js"></script>
    <script>
        CKEDITOR.replace( 'msg' );
    </script>
@endsection
