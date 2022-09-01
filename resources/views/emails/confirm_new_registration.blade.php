<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<h3>Dear {{$user->first_name.' '.$user->last_name}}</h3>
	<p>We received your registration to be a partner with {{env('APP_NAME')}} School Management System. However, you need to confirm your registration by clicking or copying the link below to your browser</p>
	<p><a href="{{route('confirm_reg', $user->confirm_hash)}}">{{route('confirm_reg', $user->confirm_hash)}}</a></p>
	<p>Your Login Credentials are:</p>
	<p>Username: {{$user->username}}</p>
	<p>Password: {{$user->username}}</p>
	<p>On first login, you will be required to change your password to whatever you wish</p>
	<p><font color="red">Please kindly ignore this message if you did not initiate this process.</font></p>

	<p>&nbsp;</p>
	<p>
		<img src="{{ url('img/logo2.png')}}" alt="www.school-point.com" width="100">
        
    </p>
    
</body>
</html>
