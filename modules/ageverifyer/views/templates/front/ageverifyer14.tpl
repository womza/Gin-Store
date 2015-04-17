<!-- ageverifyer Top  -->
{if $ageValid=="FALSE"}
	<script type="text/javascript">
    $(document).ready(function(){
	
		
        $(".fancybox_age").fancybox({
            centerOnScroll	: true,
            autoDimensions 	: false,
            padding 		   	: 10,
            width			: 500,
            height			: 500,
            autoScale     	: false,
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
        $("#returnPageNot18").bind("click", function(){
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
		  $("#returnPageNot18").bind("click", function(){
			location.href="{$backURL|escape:'htmlall':'UTF-8'}";
		  });	
		{/if}
    });
    </script>
    
    <div id="AgeVerificationTooYoung"  style="display:none">
     <a href="#contentAgeTooYoung" class="fancybox_age"></a>
        <div id="contentAgeTooYoung">
		 <div id="ageLogoTooYoung">
		 	<img id="ageLogoIMG" src="{$currentpath|escape:'htmlall':'UTF-8'}img/logoAge.jpg">
		 </div>
         	{l s='Sorry you are too young to visit our Page.'  mod='ageverifyer'}<br/>
            <button id="returnPageNot18">{l s='GO back to your previous page'  mod='ageverifyer'}</button>        
        </div>
    </div> 
 
{else if !$ageValid}
	<script type="text/javascript">
    $(document).ready(function(){
            
	
        $("#YES18").bind("click", function(){
			$.post('{$basis|escape:'UTF-8'}modules/ageverifyer/ageverifyerSESSION.php', { 
					over18: "TRUE", 
					day:$("#day").val(), 
					month:$("#month").val(), 
					year:$("#year").val(),
					token: "{$token|escape:'UTF-8'}"
					} ,
					
					
					function(data) 
					{
						 location.reload();
					} );
			
			});
			$(".fancybox_age").fancybox({
							centerOnScroll	: true,
							autoDimensions 	: false,
							padding 		   	: 10,
							width			: 500,
							height			: 500,
							autoScale     	: false,
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
    
                  
    <div id="AgeVerification" style="display:none" >
     <a href="#contentAge" class="fancybox_age"></a>      
        <div id="contentAge">
                <table border="0" cellpadding="0" cellspacing="0" id="tableContent" align="center">
                    <tr>
                     <td>
                          <div id="ageLogo">
							<img id="ageLogoIMG" src="{$currentpath|escape:'htmlall':'UTF-8'}img/logoAge.jpg">
                          </div>
                       </td>
                    </tr>
                    <tr>
                        <td align="center" class="textNote1" >
                        {l s='You must be at least 18 to enter' mod='ageverifyer'}
                     	 <p>{l s='Please verify your age.' mod='ageverifyer'}</p>
                        </td>
                       
                    </tr>
                        <tr>
                        <td align="center">
						<div align="center" class="dateCols">
						<div class="day">
                        <label for="day">{l s='Day:' mod='ageverifyer'}</label>
                   	    <select name="day" id="day">
                            <option>01</option>
                            <option>02</option>
                            <option>03</option>
                            <option>04</option>
                            <option>05</option>
                            <option>06</option>
                            <option>07</option>
                            <option>08</option>
                            <option>09</option>
                            <option>10</option>
                            <option>11</option>
                            <option>12</option>
                            <option>13</option>
                            <option>14</option>
                            <option>15</option>
                            <option>16</option>
                            <option>17</option>
                            <option>18</option>
                            <option>19</option>
                            <option>20</option>
                            <option>21</option>
                            <option>22</option>
                            <option>23</option>
                            <option>24</option>
                            <option>25</option>
                            <option>26</option>
                            <option>27</option>
                            <option>28</option>
                            <option>29</option>
                            <option>30</option>
                            <option>31</option>
                        </select>
						</div>
						<div class="month">
                        <label for="month">{l s='Month:'  mod='ageverifyer'}</label>
                        <select name="month" id="month">
                            <option value="01">{l s='January'  mod='ageverifyer'}</option>
                            <option value="02">{l s='February'  mod='ageverifyer'}</option>
                            <option value="03">{l s='March'  mod='ageverifyer'}</option>
                            <option value="04">{l s='April'  mod='ageverifyer'}</option>
                            <option value="05">{l s='May' mod='ageverifyer'}</option>
                            <option value="06">{l s='June' mod='ageverifyer'}</option>
                            <option value="07">{l s='July' mod='ageverifyer'}</option>
                            <option value="08">{l s='August' mod='ageverifyer'}</option>
                            <option value="09">{l s='September' mod='ageverifyer'}</option>
                            <option value="10">{l s='October' mod='ageverifyer'}</option>
                            <option value="11">{l s='November' mod='ageverifyer'}</option>
                            <option value="12">{l s='December' mod='ageverifyer'}</option>
                        </select>
						</div>
						<div class="year">
                         <label for="year">{l s='Year:' mod='ageverifyer'}</label>
                            <input type="text" size="4" maxlength="4" id="year" name="year" placeholder="YYYY" />
						</div>
                        </td>
                       
                    </tr>
                    <tr>
                        <td align="center">
							<div align="center">
								<button id="YES18">
									{l s='ENTER' mod='ageverifyer'}
								</button>
							</div>
                        </td>
                        
                    </tr>
                </table>
        </div>
    </div> 
{/if}
<!-- ageverifyer Top  ENDE-->