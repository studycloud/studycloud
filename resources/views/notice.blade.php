<?php if(Auth::user()->getRoles()->contains('dictator') or Auth::user()->id == $owner->id){ ?>
<li class='li_change_color'>
    <span title='{{$author}}'><b> Author: {{$author->name()}} </b></span>
    <!-- <?php if ($showDictatorPanel=true){ ?>
        <button class='notes' title='click to see dictator info' value='DummyRecipient FullNames'><img src='/images/question_mark.png' style='width:100%'></button> -->
    <?php } if (Auth::user()->id == $owner->id){ ?>
        <button class='notes' title='cancel your claim on this task' value='<?php if ($isOnlyRec=true){ echo 'disabled'; } else { echo $notice_id; } ?>'><img src='images/cancel.png' style='width:100%'></button>
    <?php } ?>
    <button class='notes' title='do something with the note' value='{{$id}}'><img src='
        <?php if($owner->name() == "No One") {
            echo '/images/claim.png';
        } elseif(Auth::user()->id == $owner->id) {
            echo '/images/done.png';
        } else {
            echo 'images/pending.png';
        } ?>' style='width: 100%'></button>
    <!-- note: it's importannt to have the buttons before the span that includes all the content so that the content will wrap around the buttons -->
    <span title='<?php echo 'DummyNote Date' ?>'
            onclick='javascript:load_page(false,'<?php echo 'DummyNote URLQuery'; ?>');'
            style='cursor:pointer;'>
    <br>Description: {{$description}}
    <br>Deadline: {{$deadline}}
    <br>Link: {{$link}}
    <br>Priority: {{$priority}}
    <br>Owner: {{$owner->name()}} </span>
</li>

<?php } ?>