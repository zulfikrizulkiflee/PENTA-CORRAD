<?php
echo '<pre>';

//https://code.google.com/p/crypto-js/#Quick-start_Guide
//echo sha1('this is the data');							//normal sha1
echo hash_hmac('sha1','this is the data','secret key');
echo '<br>';
//print_r(hash_algos());
?>
<script src="CryptoJS v3.1.2/rollups/sha1.js"></script>
<script src="CryptoJS v3.1.2/rollups/hmac-sha1.js"></script>
<script>
var hash = CryptoJS.HmacSHA1("this is the data",'secret key');
console.log(hash.toString());
</script>
<?php echo '</pre>'; ?>
