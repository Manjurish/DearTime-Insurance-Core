@extends('layouts.contentLayoutMaster')
@section('title', 'Sponsored Insurance Dashboard')
@section('content')

     <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="">
                        <canvas id="canvas1" height="900" width="1800"></canvas>
                    </div>
                </div>
            </div>
        </div>

       

    
        <div class="row">
        <div class="col-md-3">
            <div class="card">
            
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">RM {{ number_format($charityfundsum,2) }}</h3>
                                <p class="mb-0">Charity Fund</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hand-holding-heart fa-3x"></i>            
                            
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
            {{-- <a href="{{ route('admin.claim.index') }}" class="hover:bg-gray-100"> --}}
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">RM {{ number_format($soponhold_fund,2) }}</h3>
                                <p class="mb-0">Charity Fund On Hold</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hand-holding-heart fa-3x"></i>            
                            
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">{{ number_format($sopcovered) }}</h3>
                                <p class="mb-0">People got Covered So far</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hand-holding-heart fa-3x"></i>            
                            
                            </div>
                        </div>
                        
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
          
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">{{ number_format($sopinline) }}</h3>
                                <p class="mb-0">People waiting in-line</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hand-holding-heart fa-3x"></i>            
                            
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
   


@endsection

@section('myscript')
    <script src="{{ asset('/js/bootstrap-session-timeout.min.js') }}"></script>
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>

    <script>
        $.sessionTimeout({
            title: 'Session Alert',
            message : "You're session is about to expire! Would you like to stay on this page or logout?",
            keepAliveUrl: '{{ route('admin.refresh.csrf') }}',
            logoutUrl: '{{ route('logout') }}',
            redirUrl: '{{ route('logout') }}',
            warnAfter: 600000,
            redirAfter: 60000,
            countdownMessage: 'Redirecting in {timer} seconds.'
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'POST',
            url:'{{ route('admin.refresh.csrf') }}',
            success:function(data){
                $('meta[name="csrf-token"]').attr('content', data);
            }
        });

    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>

    <script>
        var labels =<?php      echo $labels; ?>;

        var users = <?php      echo $users; ?>;

        var usersData = {
            labels: labels,
            datasets: [{
                label: 'Applicants',
                backgroundColor: "pink",
                data: users
            }]
        };


       

        window.onload = function() {

            var canvas1 = document.getElementById("canvas1").getContext("2d");
            window.myBar = new Chart(canvas1, {
                type: 'line',
                data: usersData,
                options: {
                    elements: {
                        rectangle: {
                            borderWidth: 2,
                            borderColor: '#c1c1c1',
                            borderSkipped: 'bottom'
                        }
                    },
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Monthly Sponsored Insurance Applied'
                    }
                }
            });

            var canvas2 = document.getElementById("canvas2").getContext("2d");
           ;
        };

    </script>
@endsection