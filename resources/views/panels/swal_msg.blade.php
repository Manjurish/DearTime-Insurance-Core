@if (session()->has('danger_alert'))
    <script>
        Swal.fire({
            title: 'Error',
            text: '{{session()->get('danger_alert')}}',
            type: 'error',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Ok',
            confirmButtonClass: 'btn btn-primary',
            buttonsStyling: false,
        });
    </script>

@endif
@if (session()->has('success_alert'))
    <script>
        Swal.fire({
            title: 'Success',
            text: '{{session()->get('success_alert')}}',
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Ok',
            confirmButtonClass: 'btn btn-primary',
            buttonsStyling: false,
        });
    </script>
@endif
@if (session()->has('info_alert'))
    <script>
        Swal.fire({
            title: 'Success',
            text: '{{session()->get('info_alert')}}',
            type: 'info',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Ok',
            confirmButtonClass: 'btn btn-primary',
            buttonsStyling: false,
        });
    </script>
@endif
