<!-- ageverifyer Top  -->
{if $ageValid=="FALSE"}
	<script type="text/javascript">
    $(document).ready(function(){
	
        $(".fancybox_age").fancybox({
            centerOnScroll	: true,
            autoDimensions 	: true,
            padding 		   	: 10,
            width			: 500,
            height			: 500,
            autoScale     	: true,
			showCloseButton : false,
			closeBtn: false,
			enableEscapeButton: false,
			helpers : { 
			  overlay : { closeClick: false }
			},
			closeClick: false,
			  keys : {
					close  : null
				  }
            
            }).trigger('click');
		
	   
		{if $backURL == ""}
        $("#returnPageNot18").on("click", function(){
			$.post('{$basis|escape:'UTF-8'}modules/ageverifyer/ageverifyerSESSION.php', { 
					over18: "",
					token: "{$token|escape:'UTF-8'}"
					},
					function(data) 
					{
						 location.reload();
					});
				});
		{else}
		  $("#returnPageNot18").on("click", function(){
			location.href="{$backURL|escape:'htmlall':'UTF-8'}";
		  });	
		{/if}
    });
    </script>
    
    <div id="AgeVerificationTooYoung"  style="display:none">
     <a href="#contentAgeTooYoung" class="fancybox_age"></a>
        <div id="contentAgeTooYoung">
		 <div id="ageLogoTooYoung">	<img id="ageLogoIMG" src="{$currentpath|escape:'htmlall':'UTF-8'}img/logoAge.jpg"> </div>
         	{l s='Sorry you are too young to visit our Page.'  mod='ageverifyer'}<br/>
            <button id="returnPageNot18">{l s='GO back to your previous page'  mod='ageverifyer'}</button>        
        </div>
    </div> 
 
{else if !$ageValid }
	<script type="text/javascript">
    $(document).ready(function(){
            
	
        $("#YES18").on("click", function(){
			$.post('{$basis|escape:'UTF-8'}modules/ageverifyer/ageverifyerSESSION.php', 
			{ 
				over18: "TRUE",
				magicWord: "{'myLightVersion'|md5}",
				token: "{$token|escape:'UTF-8'}"
			},
				
			function(data) 
			{
				 location.reload();
			} 
			
			);
	
		});
		
        $("#NO18").on("click", function(){
			$.post('{$basis|escape:'UTF-8'}modules/ageverifyer/ageverifyerSESSION.php', 
			{ 
				over18: "FALSE",
				magicWord: "{'myLightVersion'|md5}",
				token: "{$token|escape:'UTF-8'}"
			},
				
			function(data) 
			{
				 location.reload();
			} 
			
			);
	
		});
			
		$(".fancybox_age").fancybox({
			centerOnScroll	: true,
			autoDimensions 	: true,
			padding 		   	: 10,
			width			: 500,
			height			: 500,
			autoScale     	: true,
			showCloseButton : false,
			closeBtn: false,
			enableEscapeButton: false,
			helpers : { 
			  overlay : { closeClick: false }
			},
			keys : {
					close  : null
				  }
			}).trigger('click');
		

        
    } );
    </script>
    
                  
    <div id="AgeVerification" style="display:none; " >
     <a href="#contentAge" class="fancybox_age"></a>      
        <div id="contentAge" >
                <table border="0" cellpadding="0" cellspacing="0" id="tableContent" align="center" width="100%">
                    <tr>
                     <td colspan="2">
                          <div id="ageLogo">
						  	<img id="ageLogoIMG" src="{$currentpath|escape:'htmlall':'UTF-8'}img/logoAge.jpg">
                          </div>
                       </td>
                    </tr>
                    <tr>
                        <td align="center" colspan="2" class="textNote1">
                        {l s='You must be at least 18 to enter our Page.' mod='ageverifyer'}
                        </td>
                       
					</tr>
                    <tr>
                         <td align="center">
							<button id="NO18">
								{l s='Leave' mod='ageverifyer'}
							</button>
                        </td>
						 <td align="center">
							<button id="YES18">
								{l s='ENTER' mod='ageverifyer'}
							</button>
                        </td>
                    </tr>
                </table>
        </div>
    </div> 
{/if}
<!-- ageverifyer Top  ENDE-->