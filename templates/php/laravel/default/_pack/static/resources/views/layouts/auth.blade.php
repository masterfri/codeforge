<!DOCTYPE HTML>
<html>
	<head>
		<title>
			@yield('title', 'Login')
		</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		@include('partial.common-styles')
		@include('partial.common-scripts')
		
		<script type="text/javascript">
			routing.routes = {!! jroutes() !!}
		</script>
	</head>
	<body>
		<div class="vcentered-box">
			<div class="container">
				<div id="maincontainer">
					@yield('content')
				</div>
			</div>
		</div>
		<script type="text/javascript">
			@yield('footjs')
		</script>
	</body>
</html>