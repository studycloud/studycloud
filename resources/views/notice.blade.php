<li class='li_change_color'>
    <span title='{{$author}}'><b> Author: {{$author->name()}} </b></span>
    <?php if ($showDictatorPanel=true){ ?>
        <button class='notes' title='click to see dictator info' value='DummyRecipient FullNames'><img src='/images/question_mark.png' style='width:100%'></button>
    <?php } if ($cancel_button=true){ ?>
        <button class='notes' title='cancel your completion of this task' value='<?php if ($isOnlyRec=true){ echo 'disabled'; } else { echo 'DummyNote ID'; } ?>'><img src='images/cancel.png' style='width:100%'></button>
    <?php } ?>
    <button class='notes' title='do something with the note' value='{{$id}}'><img src='<?php if($status) {echo '/images/claim.png';} ?>' style='width: 100%'></button>
    <!-- note: it's importannt to have the buttons before the span that includes all the content so that the content will wrap around the buttons -->
    <span title='<?php echo 'DummyNote Date' ?>'
            onclick='javascript:load_page(false,'<?php echo 'DummyNote URLQuery'; ?>');'
            style='cursor:pointer;'
    ><br>Description: {{$description}}
    <br>Deadline: {{$deadline}}
    <br>Link: {{$link}}
    <br>Priority: {{$priority}}
    <!-- TAKE OUT THE STATUS LATER...JUST FOR TESTING. MAKE THE BUTTON CHANGE WITH THE STATUS -->
    <br>Status: {{$status}}</span>
</li>