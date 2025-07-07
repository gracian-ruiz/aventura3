<form method="POST" action="{{ $urlTPV }}" id="redsys_form">
    <input type="hidden" name="Ds_SignatureVersion" value="{{ $signatureVersion }}">
    <input type="hidden" name="Ds_MerchantParameters" value="{{ $params }}">
    <input type="hidden" name="Ds_Signature" value="{{ $signature }}">
    <button type="submit">Pagar</button>
</form>

<script>
    document.getElementById('redsys_form').submit();
</script>
