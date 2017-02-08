<script src="/rc/js/jquery-3.1.1.min.js" type="text/javascript"></script>
<script src="/rc/js/jquery.query.js" type="text/javascript"></script>
<script src="/rc/js/handlebars.js" type="text/javascript"></script>
<script src="/rc/js/handlebars-extras.js" type="text/javascript"></script>
<script src="/rc/js/routing.js" type="text/javascript"></script>
<script src="/rc/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/rc/js/bootbox.min.js" type="text/javascript"></script>
<script src="/rc/ajaxik/ajaxik.js" type="text/javascript"></script>
<script src="/rc/ajaxik/mini-progress-bar.js" type="text/javascript"></script>
<script src="/rc/toastr/toastr.min.js" type="text/javascript"></script>
<script src="/rc/moment/moment.min.js" type="text/javascript"></script>
<script src="/rc/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script src="/rc/select2/js/select2.min.js" type="text/javascript"></script>
<script src="/rc/js/app.js" type="text/javascript"></script>
<script type="text/javascript">
	toastr.options = {
		"closeButton": true,
		"debug": false,
		"positionClass": "toast-bottom-right",
		"onclick": null,
		"showDuration": "1000",
		"hideDuration": "1000",
		"timeOut": "5000",
		"extendedTimeOut": "1000",
		"showEasing": "swing",
		"hideEasing": "linear",
		"showMethod": "fadeIn",
		"hideMethod": "fadeOut"
	}
	$(function() {
		@if (session('success'))
			toastr.success("{!! addslashes(session('success')) !!}");
		@endif
		@if (session('error'))
			toastr.error("{!! addslashes(session('error')) !!}");
		@endif
	});
</script>