<!DOCTYPE html>
<html lang="en" xmlns="https://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
    <title></title>
    <!--[if mso]>
    <style>
    table {border-collapse:collapse;border-spacing:0;border:none;margin:0;}
    div, td {padding:0;}
    div {margin:0 !important;}
    </style>
    <noscript>
    <xml>
        <o:OfficeDocumentSettings>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    </noscript>
    <![endif]-->
    <style>
        body {
            background-color: #ffffff;
        }

        table,
        td,
        div,
        h1,
        p {
            font-family: Arial, sans-serif;
            color: #000;
            color-scheme: light only;
        }

        div.col-sml {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #B1B1B1;
        }
    </style>
</head>

<body style="margin:0;padding:0;word-spacing:normal;">
<div role="article" aria-roledescription="email" lang="en"
     style="text-size-adjust:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <table role="presentation" style="width:100%;border:none;border-spacing:0;">
        <tr>
            <td align="center" style="padding:0;">
                <!--[if mso]>
                <table role="presentation" align="center" style="width:600px;">
                <tr>
                    <td>
                <![endif]-->
                <table role="presentation"
                       style="width:100%;max-width:600px;border:none;border-spacing:0;font-family:Arial,sans-serif;font-size:16px;line-height:22px;color:#ffffff;">
                    @include('mail.layout.header')
                    @yield('content')
                    @include('mail.layout.footer')
                </table>
                <!--[if mso]>
                </td>
                </tr>
                </table>
                <![endif]-->
            </td>
        </tr>
    </table>
</div>
</body>

</html>
