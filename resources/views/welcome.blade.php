<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>MLM Matrix</title>
    <style type="text/css">

        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        .matrix {
            margin: auto;
            font-size: 12px;
        }

        .matrix .depth {
            width: 680px;
            min-height: 20px;
            margin: 20px auto;
            text-align: center;
            clear: both;
            border: dashed 1px #D3D3D3;
        }

        .matrix .depth-counter {
            margin-bottom: 10px;
            display: block;
            text-align: left;
            font-weight: bold;
            padding: 10px 5px 0 10px;
        }

        .matrix .user {
            width: 45px;
            height: 45px;
            border: double 3px silver;
            overflow: hidden;
            margin: 5px auto;
        }

        .matrix .user .avatar {
            width: 45px;
            height: 45px;
            background-size: cover;
            overflow: hidden;
        }

        .matrix .user-name {
            white-space: nowrap;
        }

        .matrix .cell {
            width: 60px;
            display: inline-block;
            border: dashed 1px #D3D3D3;
            margin: 10px 0;
            padding: 5px 1px 5px 1px;
            overflow: hidden;
            text-align: center;
        }

        .matrix .matrix-join-group {
            display: inline-block;
        }

        .matrix .matrix-group-separator {
            width: 10px;
            display: inline-block;
        }

        .matrix .matrix-user-info {
            display: none
        }

        .matrix .user:hover .matrix-user-info {
            display: block;
            position: absolute;
            width: 200px;
            min-height: 30px;
            border: double 3px silver;
            background: #8BAA79;
            padding: 10px;
            margin-left: -3px;
            margin-top: -3px;
            color: white;
            font-weight: bold;
            letter-spacing: 1px;
        }
    </style>

</head>
<body>

<?= $render->show() ?>

</body>
</html>
