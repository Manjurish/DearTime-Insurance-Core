<div>
    <livewire:tables.underwriting-table></livewire:tables.underwriting-table>
    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="editModal" tabindex="-1" role="dialog"
        aria-labelledby="editModalLabel" aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Underwriting Answers</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="modal-body">
                        @if ($answers ?? null)
                            <strong>Weight</strong>: {{ $answers['weight'] ?? '-' }} <br>
                            <strong>Height</strong>: {{ $answers['height'] ?? '-' }} <br>
                            <strong>Smoke</strong>: {{ $answers['smoke'] ?? '-' }} <br><br>


                            @php
    $groupedAnswers = collect($answers['answers'] ?? [])->groupBy('question');
@endphp

@forelse ($groupedAnswers as $question => $answerGroup)
    <strong>{{ $question ?? '-' }}:</strong>
    @foreach ($answerGroup as $index => $answer)
        {{ $answer['answer'] ?? '-' }}@if(!$loop->last),@endif
    @endforeach
    <br><br>
@empty
    {{ ' ' }}
@endforelse
                        
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
@section('myscript')

    <script type="text/javascript">
        window.addEventListener('editModalHide', event => {
            $('#editModal').modal('hide');
        });
    </script>
@endsection