
<div class="add-new-data-sidebar">
    <div class="modal fade text-left" id="screening-details-modal-{{$key}}"  role="dialog" data-backdrop="false"
         aria-labelledby="myModalLabel160" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary white">
                    <h5 class="modal-title" id="myModalLabel160">{{ $row['doc']['name'] }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @php
                      $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($row['doc']), RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($iterator as $k => $v) {
                            $indent = str_repeat('&nbsp;', 10 * $iterator->getDepth());
                            // Not at end: show key only
                            if ($iterator->hasChildren()) {
                                echo "<span class='screening-details-item'> $indent$k </span><br>";
                            // At end: show key, value and path
                            } else {
                                for ($p = array(), $i = 0, $z = $iterator->getDepth(); $i <= $z; $i++) {
                                    $p[] = $iterator->getSubIterator($i)->key();
                                }
                                $path = implode(',', $p);
                                echo "<span class='screening-details-item'>$indent$k </span> $v <br>";
                            }
                        }

                    @endphp
                </div>
            </div>
        </div>
    </div>
    <div class="overlay-bg"></div>

</div>
