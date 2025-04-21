
<h1> HTML5 QR Code scanner</h1>
<div class="container">
	<div class="row">
		<div class="col-md-12" style="text-align: center;margin-bottom: 20px;">
			<div id="reader" style="display: inline-block;"></div>
			<div class="empty"></div>
			<div id="scanned-result"></div>
		</div>
	</div>
</div>

<style>
#reader {
    width: 640px;
}
@media(max-width: 600px) {
	#reader {
		width: 300px;
	}
}
.empty {
    display: block;
    width: 100%;
    height: 20px;
}
</style>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.0.3/styles/default.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.0.3/highlight.min.js"></script>
<script src="<?= base_url('assets/barcode/qrcode.js')?>"></script>
<script src="<?= base_url('assets/barcode/html5qrcode.js')?>"></script>
<script src="<?= base_url('assets/barcode/html5qrcode.scanner.js')?>"></script>
<script src="<?= base_url('assets/js/jquery.min.js')?>"></script>
<script>
function docReady(fn) {
    // see if DOM is already available
    if (document.readyState === "complete" || document.readyState === "interactive") {
        // call on next available tick
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}
docReady(function() {
	hljs.initHighlightingOnLoad();
	var results = document.getElementById('scanned-result');
	var lastMessage;
	var codesFound = 0;
	function onScanSuccess(qrCodeMessage) {
		if (lastMessage !== qrCodeMessage) {
			lastMessage = qrCodeMessage;
			++codesFound;
			results.innerHTML += `<div>[${codesFound}] - ${qrCodeMessage}</div>`;
		}
	}
	var html5QrcodeScanner = new Html5QrcodeScanner(
		"reader", { fps: 10, qrbox: 250 }, /* verbose= */ true);
	html5QrcodeScanner.render(onScanSuccess);
});
</script>