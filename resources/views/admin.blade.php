@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('css/admin.css') }}">

@endpush

@extends('layout')

@section('content')
<body>
	<div id="admin_page">
		<header id="admin_header">
			<h1 id="admin_title">Club Administration</h1>
			<div id="admin_user">
				<h3>{{Auth::user()->name()}}</h3>
				<span style="font-size: 13px;">
				{{implode(", ", Auth::user()->roles()->pluck('title')->toArray())}}
				</span>
			</div>
		</header>
		<div id="admin_notes">
			<span style="font-size: x-large;">Notice Board</span><span id="num_notice_count" style="font-size: x-large"> (0)</span>
			<br><label>Notices are refreshed every 
			<input type="number" value="60" id="seconds_delay" style="width: 40px" min="1" max="100" title="A longer refresh time means better performance."> seconds. You should also refresh the page if you've left it idle for a while. </label>
			<ul id="notes_ul">
				<!-- the javascript update_ul function puts stuff in here -->
			</ul>
			<script type="text/javascript">
				function check_if_clear(){
					if ($('#notes_ul').children('li').length <= 0){
						$('#notes_ul').append("<p id='notice_board_clear' style='margin: 1%; margin-left: 2%; font-size: larger;'>All clear! Nothing to see here.</p>");
					}
				}

				function update_ul_POSTer() {
					// $.post('includes/update_ul.php','',function(data){
					// 	if (data == "could not retrieve userid"){
					// 		location.reload();
					// 	}
					// 	$("#notes_ul").html(data);
					// 	check_if_clear();
					// 	$('#num_notice_count').text(" ("+$('.li_change_color > button[title="cancel your completion of this task"]').length+"/"+$('.li_change_color').length+")");
					// });
					// // TODO: move the html below into a new request
					$("#notes_ul").html(" \

					");
				}

				//continually update the stuffs
				function update_ul(status, interval_delay){
					if(isNaN(interval_delay)){
						interval_delay = 60000;
					}
					if (status){
						//first run the post thingy because set interval waits the inputted time before starting for the first time
						update_ul_POSTer();
						interval = setInterval(function(){
							update_ul_POSTer();
						}, interval_delay);
					} else {
						clearInterval(interval);
					}
				}
				update_ul(true); //starts the function

				$('#seconds_delay').change(function(){
					update_ul(false);
					interval_delay = $(this).val()*1000;
					update_ul(true, interval_delay);
				});

				$('#notes_ul').on('click', '.notes', function(event){
					update_ul(false);
					event.preventDefault();
					img_thingy = $(this).children('img');
					if (img_thingy.attr('src') == 'images/pending.png'){
						alert("Somebody has already reserved this task. You can't claim it anymore.");
					} else if (img_thingy.attr('src') == 'images/claim.png') {
						$.post("includes/note_pending.php",{noticeid: $(this).val(), claim: true},function(){
							update_ul(true);
						});
					} else if (img_thingy.attr('src') == 'images/done.png') {
						$.post("includes/delete_note.php",{noticeid: $(this).val()},function(){
							update_ul(true);
						});
					} else if (img_thingy.attr('src') == 'images/cancel.png') {
						if ($(this).val() == "disabled"){
							alert("You're the only one who can do this task. You can't cancel it.");
						} else {
							$.post("includes/note_pending.php",{noticeid: $(this).val(), claim: false},function(){
								update_ul(true);
							});
						}
					} else if (img_thingy.attr('src') == 'images/question_mark.png') {
						alert($(this).val());
					} else {
						alert("error");
					}
				});
			</script>
			<!-- this is where user's could create a new note themselves -->
			<!-- but I guess we've deprecated that functionality (at least for now?) -->
		</div>
			<div id="content_stuff">
				<div id="content_nav">
					<ul id='content_nav_ul'>
						<?php
							// // NOTE: changing the following php code (especially the hrefs and menu item names)
							// // will require that you make changes accordingly in the below "dictator" code
							// // and includes/load_page.php
							// if (in_array("Organizer", $admin_jobs)){
							// 	echo "<li><a href='1'>Organize Resources</a></li>";
							// }
							// if (in_array("Moderator", $admin_jobs)){
							// 	echo "<li><a href='2'>Moderate Content</a></li>";
							// }
							// if (in_array("Dictator", $admin_jobs)){
							// 	echo "<li><a href='1'>Organize Resources</a></li>
							// 		  <li><a href='2'>Moderate Content</a></li>
							// 		  <li><a href='3'>Manage Accounts</a></li>
							// 		  <li><a href='4'>Promote the Site</a></li>
							// 		  <li><a href='5'>Manage Administrators</a></li>
							// 		  <li><a href='6'>Other Stuff</a></li>";
							// }
						?>
					</ul>
					<script type="text/javascript">
						function load_page(cur_page, url_query){
							if (url_query === undefined){
								url_query = false;
							}
							$.post('includes/load_page.php',{page:cur_page, query: url_query},function(data){
								$('#actual_content').contents().remove();
								$('#actual_content').append(data);
							});
							$('html, body').animate({
								scrollTop: $("#content_nav").offset().top
							}, 1000);
						}

						$('#content_nav_ul > li > a').click(function(event){
							event.preventDefault();
							page_to_load = $(this).attr('href');

							//show that the page is selected in the nav
							$('#content_nav_ul li a').css("background-color","#dddddd");
							$(this).css("background-color","#b5b5b5");

							//set the background of the div to be loading
							if (!$('#actual_content').hasClass("add_background_to_actual_content")){
								//$('#actual_content').addClass("add_background_to_actual_content");
							}

							//load the page
							load_page(page_to_load);
						});
					</script>
				</div>
				<div id="actual_content">
					<div style="margin-top: 1%; text-align: center; margin-bottom: 1%">Click on a thingy above to open a page.</div>
				</div>
				<div id="flagged_stuff">
					<!-- *TODO: manage files (disable or enable)-->
					<!-- TODO: search files -->
					<!-- *TODO: manage comments (disable or enable) -->
					<!-- TODO: search comments -->
				</div>
				<div id="rec_resources">
					<!-- *TODO: manage subtopics (disable or enable)-->
					<!-- TODO: search subtopics -->
					<!-- TODO: display feed of recently created subtopics -->
					<!-- TODO: display feed of recently created topics and subclasses (allow for enable, disable, and editing) -->
				</div>
				<div id="user_accounts">
					<!-- TODO: put a search box for people's accounts -->
					<!-- TODO: allow for sending customized notifications to the user -->
					<!-- TODO: receive notification for password reset -->
					<!-- TODO: edit user account (grade, email, password reset, name, classes, admin choice referred to dictator?) -->
					<!-- TODO: disable user account -->
					<!-- TODO: enable and view new user account -->
					<!-- TODO: receive notification if new user account has the same full name as a previously created account -->
				</div>
				<!-- TODO: add or enable/disable classes with a search for disabled ones-->
				<!-- TODO: add or delete about videos -->
				<!-- TODO: at the beginning of each year, raise everyone's grades up by 1 and disable senior accounts -->
				<!-- TODO: at the end of each year, send a notification to the admin (idk which job yet) about which user has the best score on the stats page and suggest that they lead study cloud for the upcoming school year -->
				<!-- TODO: dictator must be able to monitor the admin pages of other admins (which tasks they've completed) -->
				<!-- TODO: dictator must be able to assign, rearrange, and add (dictator chooses from given functions list?) jobs -->
				<!-- TODO: easter egg functionality???? -->
				<!-- TODO: make a sidebar with general broad functions -->
				<!-- TODO: create a constitution panel that expands vertically -->
			</div>
		</div>
</body>
@stop
