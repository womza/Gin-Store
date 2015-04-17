<img class="imagen" src="/modules/easycaptcha/showCaptcha.php" />
<i class="refresh"></i>
<input type="text" placeholder="{l s='Introduce las letras' mod='easycaptcha'}" name="captcha"/>
{literal}
<script>
	$('.refresh').on({
    'click': function(){
         $('.imagen').attr('src','/modules/easycaptcha/showCaptcha.php');
    }
});
</script>
{/literal}
<style>
	i.refresh{
		background:url('{$modules_dir}easycaptcha/refresh.gif');
		width: 17px;
		height: 15px;
		display: inline-block;
		margin-left: 10px;
		cursor: pointer;
	}
</style>


