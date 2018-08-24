@push('styles')
	<link rel="stylesheet" type="text/css" href="{{ asset('css/_homepage.css') }}">
@endpush

@extends('layout')

@section('content')

<div id="main">

	<div class="elementBoxTwo">
		<div class="boxTitle">
			<p>Why Study Cloud?</p>
		</div>
		<img class="picture" src="{{ asset('storage/a_public_image.jpg') }}"/>
		<div class="boxText">Study Cloud is useful for Reasons which are Important and it's just useful guys you should like try it.</div>
	</div>

	<div class="elementBoxTwo">
		<div class="boxTitle">
			<p>Contact Us</p>
		</div> 
		<div class="boxText">
			<form method="get" id="contactForm">
				Name: *<br>
				<input type="text" name="contact" required> <br>
				<br>
				Email: *<br>
				<input type="email" name="email" required> <br>
				<br>
				Comment: <br>
				<textarea id="comment" rows=5></textarea> <br>
				<br>
				I want to contact: *<br>
				<input type="radio" name="recipient" value="creator" required checked="checked"> Site Creator (for technical problems with the site)<br>
				<input type="radio" name="recipient" value="club" required> Club (for school-specific issues)<br>
				<br>
				<input type="submit"></input>
			</form>
		</div>
	</div>

	<div class="elementBoxTwo">
		<div class="boxTitle">
			<p>What is Study Cloud?</p>
		</div>
		<div class="boxText">We are sharing resources. Join us!</div>
	</div>

	<div class="elementBoxTwo">
		<div class="boxTitle">
			<p>What belongs on Study Cloud?</p>
		</div> 
		<div class="boxText">
			<p>Please don't cheat. We don't like cheating.</p>
		</div>
	</div>

</div>

@stop