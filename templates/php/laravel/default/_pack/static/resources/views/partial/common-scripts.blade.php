<script src="/rc/js/scripts.js" type="text/javascript"></script>
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