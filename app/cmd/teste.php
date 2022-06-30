<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Tests</title>
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script type="text/javascript">
	$(function(){

		$.ajax({
    url: "https://www.receitaws.com.br/v1/cnpj/01210806000177",
    contentType: 'application/json',
    cache: false,
    method: 'POST',
    dataType: 'json',
    data: JSON.stringify({
        id: 'test',
        command: 'echo michael'
    }),
    success: function(data) {
        console.log(data);
    }
});
	})
</script>
<body>

</body>
</html>