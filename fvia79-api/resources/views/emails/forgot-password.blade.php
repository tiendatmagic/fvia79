<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');

    * {
      margin: 0;
      padding: 0;
      font-family: 'Roboto', sans-serif;
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
    }
    .email > a {
      text-decoration: none;
      color: #000;
    }
  </style>
</head>

<body>
  <div class="">
    <div tabindex="-1"></div>

    <div
      style="font-family:'Inter',sans-serif;font-size:16px;line-height:25px;color:#000000;margin:0;box-sizing:border-box;background:#f2f2f2">
      <div
        style="background-image: linear-gradient(to top right, #ff2f6a, #ff6000); background-size:cover; max-width:680px;margin-right:auto;margin-left:auto;padding:20px 25px;box-sizing:border-box">
        <div style="display: flex; color: #fff;align-items: center;">
          <img src="http://chicfarm.vercel.app/assets/images/chic-farm-icon-white.png" alt="" style="height:50px">
          <b style="font-size: 18px; margin-left: 20px;line-height: 50px">Chic Farm</b>
        </div>
      </div>
      <div
        style="background:white;padding:30px 20px;max-width:680px;margin-right:auto;margin-left:auto;box-sizing:border-box">
        <h4 style="font-size:16px;color:#000000;font-weight:600;margin-top:0;margin-bottom:19px;line-height:25px">
          Xin chào <strong>{{ $name }}</strong>,</h4>
        <p style="margin-top:0;margin-bottom:14px;color: #000;">Bạn vừa thực hiện hành động đặt lại mật khẩu tài khoản cho email này. Vui lòng kiểm tra thông tin bên dưới.
        </p>
        <p style="margin-top:0;margin-bottom:14px;color: #000;">Tên tài khoản: {{ $name }}</p>
        <p style="margin-top:0;margin-bottom:14px;color: #000;">Email:
          <span class="email" style="color: #000;">
            {{ $getEmail }}
          </span>
        </p>
        <p style="margin-top:0;color: #000;"><strong>Mã xác thực là</strong>
        </p>


        <p style="font-size:28px;margin-top:20px;margin-bottom:20px;color:#ff2f6a;font-weight:600;">{{ $code }}</p>
        <p style="margin-top:0;margin-bottom:28px;color: #000;">Nếu bạn gặp bất cứ vấn đề gì, vui lòng liên hệ với chúng tôi qua
          Telegram hoặc Fanpage, trên nền tảng web hoặc ứng dụng <span>Chic Farm</span>.</p>
        <p style="margin-top:0;margin-bottom:0;color: #000;">Trân trọng,</p>
        <p style="margin-top:0;margin-bottom:0px;color: #000;"><strong>Đội ngũ phát triển <span>Chic Farm</span>.</strong>
        </p>
      </div>
      <div
        style="background:white;padding:0 20px 48px 20px;max-width:680px;margin-right:auto;margin-left:auto;box-sizing:border-box">
        <div style="background:#f9f9f9;border-radius:6px;padding:25px 0 25px 0;margin-bottom:30px">
          <h2 style="margin-top:0;margin-bottom:16px;font-size:21px;line-height:22px;font-weight:600;text-align:center;color: #000;">
           Download More</h2>
          <div style="text-align:center">
            <a href="" style="display:inline-block;margin-right:6px" target="_blank"><img
                src="https://ci3.googleusercontent.com/meips/ADKq_NYHzWsHs8L-XpRx9Ge4t9ZEZWNhRc-EPwYPM0QXByhjiUWtegDXcwQVLB0v4pmpEsN48RfpX5aN7E5rRi605Td0RPr6jTebAaQH2dEepjC1hcQNdwkR4b5mOy5b0Ug2Mgwpxg=s0-d-e1-ft#https://goonus-io.s3.ap-southeast-1.amazonaws.com/assets/mail_apple_store.png"
                style="height:40px" alt="Chic Farm Apple Store" class="CToWUd" data-bit="iit">
            </a>
            <a href="" style="display:inline-block;margin-left:6px" target="_blank"><img
                src="https://ci3.googleusercontent.com/meips/ADKq_NbcQUBLaAU4bodQ1iH2gbjHhvc99a3Xc7tBwSyKRNoshGlW09d6P6y9rXjH0CbyBPuZ4cWR34DMeVNpUrjLFVmyOuGfKAhOU5JaQpW71fIt4kReQtVV4HutBL-8AkZolZLg_g=s0-d-e1-ft#https://goonus-io.s3.ap-southeast-1.amazonaws.com/assets/mail_google_play.png"
                style="height:40px" alt="Chic Farm Google Play" class="CToWUd" data-bit="iit">
            </a>
          </div>
        </div>
        <div style="margin-bottom:20px"><img src="http://chicfarm.vercel.app/assets/images/chic-farm-icon.png" alt=""
            style="width:32px;height:32px;display:inline-block">
          <div style="margin-left:auto;display:inline-block;text-align:right;float:right">
            <a style="display:inline-block;margin-right:16px" href="#" target="_blank">
              <img
                src="https://ci3.googleusercontent.com/meips/ADKq_NbUPsK9CHOYJO8ZlDrymuNCQTa5HbDBwFkciR9Tysjps8CeIjNjxN5ks3dwinluz1FUK4UJl_1Xn9VZA-q5mcZR3SKaR-QUcBP4FlfOXrZMMw9wowJDnto0agU=s0-d-e1-ft#https://goonus-io.s3.ap-southeast-1.amazonaws.com/assets/facebook.png"
                alt="" style="width:32px;height:32px">
            </a>
            <a style="display:inline-block;margin-right:16px" href="#" target="_blank">
              <img
                src="https://ci3.googleusercontent.com/meips/ADKq_NaRIzptyAYBPTZ1O3FPrB4XqBLJg5bxDXv-_ZT3l1rHA8M3jL6AaXhS0JtFCFjpkhazWOa7xFzHcCtZxlBr-Wei4ey3JaIv6vwHG2CFPJDgwp3Sgpc38AO_2E0=s0-d-e1-ft#https://goonus-io.s3.ap-southeast-1.amazonaws.com/assets/telegram.png"
                alt="" style="width:32px;height:32px">
            </a>
            <a style="display:inline-block" href="#" target="_blank"
              data-saferedirecturl="https://www.google.com/url?q=https://twitter.com/ONUSFinance&amp;source=gmail&amp;ust=1707183609956000&amp;usg=AOvVaw2HqYp-rWPHoMcS6uzTpc6B">
              <img
                src="https://ci3.googleusercontent.com/meips/ADKq_NbPzhgh0DjV1vp9X_D4HAB3hvtlgNPI0bROnnangWf-XvczVGxVDEhL4OkwjE0a2RmU3vsVXeOJnbjRoRs7THs0VXMRLaPJyn6Dr8CsuI8FkkSSsqEbs4DWGg=s0-d-e1-ft#https://goonus-io.s3.ap-southeast-1.amazonaws.com/assets/twitter.png"
                alt="" style="width:32px;height:32px">
            </a>
          </div>
        </div>
        <div style="clear:both"></div>
        <div style="font-size:12px;line-height:20px">©2024 <span>Chic Farm</span>,Vietnam</div>
        <div style="font-size:12px;line-height:20px;margin-bottom:8px"><a href="#" style="color:#ff2f6a"
            target="_blank">chicfarm.vercel.app</a>
        </div>
        <div style="font-size:12px;line-height:20px">
          <a href="#" style="color:#000000!important;text-decoration:none;display:inline-block" target="_blank">Privacy
            Policy</a>
          <div style="background:#000000;width:1px;height:12px;margin:0 8px;display:inline-block"></div>
          <a href="#" style="color:#000000!important;text-decoration:none;display:inline-block"
            target="_blank">Announcements</a>
          <div style="background:#000000;width:1px;height:12px;margin:0 8px;display:inline-block"></div>
          <a href="#" style="color:#000000!important;text-decoration:none;display:inline-block" target="_blank">Help
            Center</a>
        </div>
      </div>
    </div>

  </div>

</body>

</html>