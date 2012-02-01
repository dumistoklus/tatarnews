<?php
    LoadViewTemplate::load('base_header', __FILE__);
	$body = BodyViewData::get();

?>
<body>
<div id="all">
<!--<div style="position: absolute; z-index: 1; width: 100%; top: 0;"><a href="http://www.gatar.ru" rel="nofollow" style="display:block; width: 181px; height: 507px; background: url(/images/brbrbr/gatar.jpg); margin: 0 auto; position: relative; left: -657px; z-index:2"></a></div>-->
	<div id="wrapper">
		<div id="header">
			<?php echo $body->header(); ?>
		</div>
		<!-- #header-->
		<div id="middle">
			<table style="width: 100%;">
                <tr>
                    <td id="container"><?php echo $body->center(); ?></td>
                    <td id="sideLeft"><?php echo $body->left(); ?></td>
                    <td id="sideRight"><?php echo $body->right(); ?></td>
                </tr>
            </table>
		</div>
		<!-- #middle-->
		<div id="footerwarp">
			<?php echo $body->bottom(); ?>
			<div><?php @include("{$_SERVER['DOCUMENT_ROOT']}/klinks/klinks.php"); ?></div>
			<div id="copyright">&copy; <?php echo date('Y'); ?> ООО &laquo;Известия Татарстана&raquo;</div>
		</div>
		<!-- #footer -->
	</div>
<!-- #wrapper -->
</div>
<div id="overlay"></div>
<div id="popup-warp">
        <div class="popup-fix">
            <div class="popup-box" id="box-login">
                <div class="popup-header">
                    <a href="#" title="Закрыть" class="popup-close"></a>
                    Авторизация
                </div>
                <div class="popup-body">
                    <div class="mini-logo"></div>
	                <div class="warning hide">
                       Не верный e-mail или пароль
                    </div>
                    <form method="post" action="/" id="signinform">
                        <table style="width: 100%;">
                            <tr>
                                <td class="input-container"><div class="form-indent hint-warp">
                            <span class="login-hint">
                            E-mail
                            </span>
                            <input type="text" name="login" class="typetext" id="signin-login" />
                        </div></td>
                                <td><div class="icon-checked"></div>
                                    <div class="icon-notchecked"></div></td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td class="input-container">
                        <div class="form-indent hint-warp">
                            <span class="login-hint">
                            Пароль
                            </span>
                            <input type="password" name="password" class="typetext" id="signin-password" />
                        </div>
                        </td>
                                <td><div class="icon-checked"></div>
                                    <div class="icon-notchecked"></div></td>
                            </tr>
                        </table>
	                    <div class="form-indent">
                            <label>
                                <input type="checkbox" name="rememberme" />
                                <span class="checkbox-fix">
                                Запомнить меня
                                </span>

                            </label>
                        </div>
                        <table class="social-table">
                            <tr>
                                <td><a href="#" class="icon-fb"></a></td>
                                <td>&nbsp;</td>
                                <td><a href="#" class="icon-vk"></a></td>
                            </tr>
                        </table>

                        <div class="form-second-indent">
                            <input type="submit" value="Войти" style="padding: 1px 25px;" />
                        </div>
                    </form>
                </div>
            </div>
            <div class="popup-box" id="box-registration">
                <div class="popup-header">
                    <a href="#" title="Закрыть" class="popup-close"></a>
                    Регистрация
                </div>
                <div class="popup-body">
                    <div class="mini-logo">
                    </div>
                    <form method="post" action="/?page=registration" id="registration-form">
                        <table style="width: 100%;">
                            <tr>
                                <td class="input-container"><div class="form-indent hint-warp">
                                        <span class="login-hint">
                                        Email
                                        </span>
                                        <input type="text" name="email" class="typetext" id="email" />
                                    </div></td>
                                <td><div class="icon-checked"></div>
                                    <div class="icon-notchecked"></div></td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td class="input-container"><div class="form-indent hint-warp">
                                        <span class="login-hint">
                                        Пароль
                                        </span>
                                        <input type="password" name="password" class="typetext" id="password" />
                                    </div></td>
                                <td><div class="icon-checked"></div>
                                    <div class="icon-notchecked"></div></td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td class="input-container"><div class="form-indent hint-warp">
                                        <span class="login-hint">
                                        Повторите пароль
                                        </span>
                                        <input type="password" name="password2" class="typetext" id="passwordagain" />
                                    </div></td>
                                <td><div class="icon-checked"></div>
                                    <div class="icon-notchecked"></div></td>
                            </tr>
                        </table>
                        <table class="social-table">
                            <tr>
                                <td><a href="#" class="icon-fb"></a></td>
                                <td>&nbsp;</td>
                                <td><a href="#" class="icon-vk"></a></td>
                            </tr>
                        </table>
                        <div class="form-second-indent">
                            <input type="submit" value="Зарегистрироваться" style="padding: 1px 10px;" />
                        </div>
                    </form>
                </div>
            </div>
    </div>
</div>
<!-- Yandex.Metrika counter -->
<div style="display:none;"><script type="text/javascript">
(function(w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter7661671 = new Ya.Metrika({id:7661671, enableAll: true});
        }
        catch(e) { }
    });
})(window, 'yandex_metrika_callbacks');
</script></div>
<script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
<noscript><div><img src="//mc.yandex.ru/watch/7661671" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
</body>
</html>