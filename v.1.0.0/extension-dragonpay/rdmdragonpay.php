<?php
## no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
## Import library dependencies
jimport('joomla.plugin.plugin');






 
class plgRDmediaRDMdragonpay extends JPlugin
{
/**
 * Constructor
 *
 * For php4 compatability we must not use the __constructor as a constructor for
 * plugins because func_get_args ( void ) returns a copy of all passed arguments
 * NOT references.  This causes problems with cross-referencing necessary for the
 * observer design pattern.
 */
 function plgRDMediaRDMdragonpay( &$subject, $params  ) {
 
    parent::__construct( $subject , $params  );
	
	## Loading language:	
	$lang = JFactory::getLanguage();
	$lang->load('plg_rdmedia_dragonpay', JPATH_ADMINISTRATOR);	

	## load plugin params info
 	$plugin = JPluginHelper::getPlugin('rdmedia', 'rdmdragonpay');

	$this->merchantid 		= $this->params->def( 'merchantid', 'xxxxxxxxx' );
	$this->ccy 	        	= $this->params->def( 'ccy', 'PHP' );
	$this->dragonpay_api_password 	= $this->params->def( 'dragonpay_api_password', 'xxxxxxxxx' );
	$this->sandbox_on 		= $this->params->def( 'sandbox_on', 1 );

        $this->debug_on 		= $this->params->def( 'debug_on', 1 );
        $this->dragonpay_param2 	= $this->params->def( 'shopping_cart_id', 'xxxxxxxxx' );
        $this->notification_parser 	= $this->params->def( 'notification_parser', 1 );


	$this->success_tpl 		= $this->params->def( 'success_tpl', 1 );
	$this->failure_tpl 		= $this->params->def( 'failure_tpl', 1 );
	$this->itemid 			= $this->params->def( 'itemid', 1 );
	$this->layout 			= $this->params->def( 'layout', 1 );	
	$this->mail_on_notification 	= $this->params->def( 'mail_on_notification', 0 );	
	$this->notify_email 	        = $this->params->def( 'notify_email', 0 );	
	$this->notify_email_msg         = $this->params->def( 'notify_email_msg', '0' );
	$this->cancel_tpl 		= $this->params->def( 'cancel_tpl', '0' );
	
	## Including required paths to calculator.
	$path_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'helpers'.DS.'get.amount.php';
	include_once( $path_include );

	## Getting the global DB session
	$session = JFactory::getSession();
	## Gettig the orderid if there is one.
	$this->ordercode = $session->get('ordercode');
	
	## Getting the amounts for this order.
	$this->amount = _getAmount($this->ordercode);
	$this->fees	  = _getFees($this->ordercode); 




        ## This is where the data is being processed..
        $this->posturl = JURI::root().'index.php?option=com_ticketmaster&view=transaction&payment_type=dragonpay';

	## Return URLS to your website after processing the order at Dragonpay.
	$this->return_url = JURI::root().'index.php?option=com_ticketmaster&view=transaction&payment_type=dragonpay';
	
	## IPN messenger URL:
	$this->notify_url = JURI::root().'index.php?option=com_ticketmaster&controller=ipn&task=ipnProcessor&plg=rdmdragonpay';
	



	## Use the sandbox if you're testing. (Required: Sandbox Account with Dragonpay)
	if ($this->sandbox_on == 1){
		## We're in a testing environment.
		$this->url = 'https://test.dragonpay.ph/Pay.aspx';                            

	}else{
		## Use the lines below for a live site.
		$this->url = 'https://gw.dragonpay.ph/Pay.aspx';                
	}
	
 }
 


/**
 * Plugin method with the same name as the event will be called automatically.
 * You have to get at least a function called display, and the name of the processor (in this case Dragonpay)
 * Now you should be able to display and process transactions.
 * 
*/







	 function display()
	 {
		$app = &JFactory::getApplication();
		
		## Loading the CSS file for Dragonpay plugin.
		$document = &JFactory::getDocument();
		$document->addStyleSheet( JURI::root(true).'/plugins/rdmedia/rdmdragonpay/rdmedia_dragonpay/css/dragonpay.css' );	
		
		$user =& JFactory::getUser();





              ## Making sure Dragonpay getting the amount format to have format number like this:  xxxxxxxxxxxx.xx  
	      $ordertotal = number_format($this->amount, 2, '.', '');



		## Check the amount, if higher then 0.00 then show the plugin data.	
		if ($ordertotal > '0.00') {
			
			## Check if this is Joomla 2.5 or 3.0.+
			$isJ30 = version_compare(JVERSION, '3.0.0', 'ge');
			
			## This will only be used if you use Joomla 2.5 with bootstrap enabled.
			## Please do not change!
			
			if(!$isJ30){
				if($config->load_bootstrap == 1){
					$isJ30 = true;
				}
			}	
			




			if($this->layout == 1 ){

                            
                                   echo '<img src="plugins/rdmedia/rdmdragonpay/rdmedia_dragonpay/images/dragonpay_vertical_view.png" />';
				
			 
			## Form to show to client: Make Payment.
                         echo '<form action="'.$this->posturl.'" method="post" >';
				
                                   echo '<input type="hidden" name="submit" />';	                      

			      echo     '<button class="btn btn-block btn-success" style="margin-top: 8px;" type="submit" name="submit">'.JText::_( 'Make Payment' ).'</button>';				
				
							
			 echo '</form>';


					
				
			}else{
			

                                       echo '<img src="plugins/rdmedia/rdmdragonpay/rdmedia_dragonpay/images/dragonpay_vertical_view.png" />';                                    echo '<br />';
                                       

                                ## Form to show to client: Make Payment.
				echo '<form action="'.$this->posturl.'" method="post" >';

                                       										
				       echo '<input type="hidden" name="submit" />';                                        
								
					
				    echo '<input type="submit" name="submit" value="Make Payment" style="color: #FFFFFF; background-color: #58b058; width: 325px; height:30px; border:1px solid #000000">';
									
							
				echo '</form>';


                               			
			}
		
		}
		
		return true;
	 }



















// when checkout button is clicked
 function dragonpay()
	 {

           if(isset($_POST['submit']))  {



clearstatcache() ;







# NOTE: Perform the correct action based on the $data['status'] value (i.e. if expired, refetch data, etc.)

#







		// Load user_profile plugin language
		$lang = JFactory::getLanguage();
		$lang->load('plg_rdmedia_dragonpay', JPATH_ADMINISTRATOR);
	
		## Include the confirmation class to sent the tickets. 
		$path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'createtickets.class.php';
		$override = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'override'.DS.'createtickets.class.php';	
		
		
		$user = JFactory::getUser();
		$db   = JFactory::getDBO();
		
		
		$client = $db->loadObject();



                ## Making sure Dragonpay getting the amount format to have format number like this:  xxxxxxxxxxxx.xx  
	      $ordertotal = number_format($this->amount, 2, '.', '');




              ## Hostname of Joomla install
              $hostname = $_SERVER['HTTP_HOST']; 


              ## Dragonpay transaction description
              $description = 'Your Order on '.$hostname;



              ## purge old values if there are
              $digest_str = "";
              $digest = "";
              $request_params = "";
              $url_request_params = "";
              $param1 = "";
              $param2 = "";


              ## create the digest for Dragonpay
              $digest_str = $this->merchantid.':'.$this->ordercode.':'.$ordertotal.':'.$this->ccy.':'.$description.':'.$user->email.':'.$this->dragonpay_api_password ;
     
                      ## to create 40 Char sha1
                      $digest = sha1($digest_str, $raw_output = false);

                     ## As per Dragonpay requirement, param1 & param2 are not used to create the above digest
                        /** As by default ordertotal is not posted back by Dragonpay and even not stored natively in ticketmaster
                            order table, so, it's just more easy to use available custom param that will be posted back 
                            to us by Dragonpay, ordertotal is not only tickets sum in the order, but it's take also consideration of
                            discout/coupon  */
                       $param1 = $ordertotal ;  
                      // $param2 = "";            # available to use
                       $param2 = $this->dragonpay_param2;



              ### Let's prepare the send to Dragonpay:   using urlencode to get URL format
                  $request_params = "merchantid=" . urlencode($this->merchantid) .
		      "&txnid=" .  urlencode($this->ordercode) . 
		      "&amount=" . urlencode($ordertotal) .
		      "&ccy=" . urlencode($this->ccy) .
		      "&description=" . urlencode($description) .
		      "&email=" . urlencode($user->email) .
		      "&digest=" . urlencode($digest) .
                      "&param1=" . urlencode($param1) .
                      "&param2=" . urlencode($param2);


              


                $url_request_params = $this->url .'?'. $request_params;


                ## Let's go to Dragonpay website
                $url_request_params = str_replace('preparse', "index", $url_request_params); # optional (can work without this line)

		header("Location: $url_request_params");  // this can be disable for TEST a such way to see above echo







        if ($this->debug_on == 1){ 

            ## For debug/check purpose

                    // Create ManilaTime variable
                    function ManilaTime1() {
                    date_default_timezone_set('Asia/Manila');
                    $date = new DateTime();
                    echo $date->format('Y-m-d H:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
                    // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise
                    }
                    ob_start();
                    ManilaTime1();
                    $ManilaTime = ob_get_clean();


             ## writte above variable values in a file for check purpose
                $dragonpay_transaction_start = $ManilaTime. '  Transaction Initiated';
                $dragonpay_transaction_start_underline = "--------------------------------------------------------------------";
                $php_start = "<?php";
                $php_end = "?>";
           
                    // debug_log.php 
                   // for security we register in .ph file & with php code start & php code end
                   $fp = fopen(dirname(__FILE__).'/debug_log.php', 'a');  
                       fwrite($fp, $php_start."\n");                     
                       fwrite($fp, $dragonpay_transaction_start."\n");
                       fwrite($fp, $dragonpay_transaction_start_underline."\n");
                       fwrite($fp, "url_request_params:   ".$url_request_params."\n");
                   //  fwrite($fp, "digest_str:   ".$digest_str."\n"); // As it's contain dragonpay api password, only write it for test
                       fwrite($fp, "digest:   ".$digest."\n");
                       fwrite($fp, "param1:   ".$param1."\n");
                       fwrite($fp, "param2:   ".$param2."\n");
                       fwrite($fp, $php_end."\n");
                 fclose($fp);
         }














              // Better to do not remove variables session here in case customer
             // do back in his Internet browser before to process completely with Dragonpay
                 /**
                    ## Removing the variables session, it's not needed anymore.
		    $session = JFactory::getSession();
		    $session->clear($this->ordercode);
		    $session->clear('ordercode');
		    $session->clear('coupon');
		*/


	


     } // END:     LICENSE IS VALID STATUS




  } # END:  if form (order) is submited to Dragonpay

















////  SECTION WEB RETURN RECEPTION


## Get Dragonpay answer done to our return URL: Web redirection & posted data by Dragonpay
 ## N.B.: Transaction, order or ticket are not updated in db/table from here (WebReturn) but only from the Dragonpay IPN
 ## N.B.: generate eticket, send email are not done from here (WebReturn) but only from the Dragonpay IPN

## example of Web redirection from Dragonpay:
/** 
  http://dragonpay-ticketmaster.netpublica.ph/index.php?option=com_ticketmaster&view=transaction&payment_type=dragonpay&txnid=1281212&refno=5XVTS7B5&status=S&message=[000]+BOG+Reference+No%3a+20141013150757+%235XVTS7B5&digest=55d5ffbf360f5d538d1572d86979ab3e954eda57&param1=9000.00
*/
  

    ## Purge old values
    $dragonpay_webReturn_txnid = "";
    $dragonpay_webReturn_refno = "";
    $dragonpay_webReturn_status = "";
    $dragonpay_webReturn_message = "";
    $dragonpay_webReturn_digest = "";
    $dragonpay_webReturn_param1 = "";
    $dragonpay_webReturn_param2 = "";



    ## Get data posted by Dragonpay
    $jinput = JFactory::getApplication()->input;
    $dragonpay_webReturn_txnid = $jinput->get('txnid', '', 'STRING');
    $dragonpay_webReturn_refno = $jinput->get('refno', '', 'STRING');
    $dragonpay_webReturn_status = $jinput->get('status', '', 'STRING');
    $dragonpay_webReturn_message = $jinput->get('message', '', 'STRING');
    $dragonpay_webReturn_digest = $jinput->get('digest', '', 'STRING');
    $dragonpay_webReturn_param1 = $jinput->get('param1', '', 'STRING');
    $dragonpay_webReturn_param2 = $jinput->get('param2', '', 'STRING');








     ## To check later the authenticity of the digest  
       ## Purge old values
       $webReturn_digest_str = "";
       $true_webReturn_digest = "";
       ## As per Dragonpay requirement, param1 & param2 are not used to create the below digest 
        ## N.B.: strings bellow to calculate the digest are string in an URL DECODE format.   
       $webReturn_digest_str = $dragonpay_webReturn_txnid.':'.$dragonpay_webReturn_refno.':'.$dragonpay_webReturn_status.':'.$dragonpay_webReturn_message.':'.$this->dragonpay_api_password ;
	     
       $true_webReturn_digest = sha1($webReturn_digest_str, $raw_output = false);   ## to create 40 Char sha1



 


    
        if ($this->debug_on == 1){       

            ## For debug/check purpose

                    // Create ManilaTime variable
                    function ManilaTime2() {
                    date_default_timezone_set('Asia/Manila');
                    $date = new DateTime();
                    echo $date->format('Y-m-d H:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
                    // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise
                    }
                    ob_start();
                    ManilaTime2();
                    $ManilaTime = ob_get_clean();


              ## writte above variable values in a file for check purpose
                $dragonpay_webReturn_start = $ManilaTime. '  WebReturn Received';
                $dragonpay_webReturn_start_underline = "--------------------------------------------------------------------";
                $php_start = "<?php";
                $php_end = "?>";

               ## Check if there was ever an webReturn done (if not no need yet to write data as it will give wrong "true" digest)
               if ($dragonpay_webReturn_txnid != "") {
                  // debug_log.php 
                 // for security we register in .ph file & with php code start & php code end
                 $fp = fopen(dirname(__FILE__).'/debug_log.php', 'a');  
                       fwrite($fp, $php_start."\n"); 
                       fwrite($fp, $dragonpay_webReturn_start."\n"); 
                       fwrite($fp, $dragonpay_webReturn_start_underline."\n");
                       fwrite($fp, "webReturn_txnid:   ".$dragonpay_webReturn_txnid."\n");                        
                       fwrite($fp, "webReturn_refno:   ".$dragonpay_webReturn_refno."\n"); 
                       fwrite($fp, "webReturn_status:   ".$dragonpay_webReturn_status."\n");  
                       fwrite($fp, "webReturn_message:   ".$dragonpay_webReturn_message."\n"); 
                       fwrite($fp, "webReturn_digest:   ".$dragonpay_webReturn_digest."\n");
                       fwrite($fp, "webReturn_param1:   ".$dragonpay_webReturn_param1."\n");
                       fwrite($fp, "webReturn_param2:   ".$dragonpay_webReturn_param2."\n");  
                       fwrite($fp, "true_webReturn_digest:   ".$true_webReturn_digest."\n"); 
                       fwrite($fp, $php_end."\n");                           
                fclose($fp);
                }
            }











 ## Check the digest of webReturn
 if ($dragonpay_webReturn_digest == $true_webReturn_digest) {           # Disable this line to test Webreturn different templateS display





    
      if ($dragonpay_webReturn_status == 'S'){

              ## THE PAYMENT WENT TOTALLY SUCCESSFUL     "S"     SUCCESS   STATUS  ##

               ## Show transaction SUCCESS message to the client.
                                                              
                       require_once (dirname(__FILE__).'/rdmedia_dragonpay/templates/webReturn_tpl_success.php');

                      # echo $this->success_tpl; #this can also work if this variable is definited in the .xml file & in above class variables




               ## Removing the variables session, it's not needed anymore.
		$session = JFactory::getSession();
		$session->clear($this->ordercode);
		$session->clear('ordercode');
		$session->clear('coupon'); 
                  





	}elseif($dragonpay_webReturn_status == 'F'){	
			
                   ## THE PAYMENT WENT TOTALLY WRONG     "F"     FAILURE   STATUS  ##
 
                     ## Show transaction FAILURE message to the client.
                      
                          require_once (dirname(__FILE__).'/rdmedia_dragonpay/templates/webReturn_tpl_failure.php');
	           
                          # echo $this->failure_tpl; #this can also work if this variable is definited in the .xml file & in above class variables


                    ## Removing the variables session, it's not needed anymore.
		    $session = JFactory::getSession();
		    $session->clear($this->ordercode);
		    $session->clear('ordercode');
		    $session->clear('coupon');




                    }elseif($dragonpay_webReturn_status == 'P'){
			
			## THE PAYMENT HAVE      "P"   PENDING STATUS ##
			
                          ## Show transaction PENDING message to the client.
                                             
                                require_once (dirname(__FILE__).'/rdmedia_dragonpay/templates/webReturn_tpl_pending.php');
                            
                                # echo $this->pending_tpl; #this can also work if this variable is definited in the .xml file & in above class variables



                    ## Removing the variables session, it's not needed anymore.
		    $session = JFactory::getSession();
		    $session->clear($this->ordercode);
		    $session->clear('ordercode');
		    $session->clear('coupon');




		}elseif($dragonpay_webReturn_status == 'U'){
			
			## THE PAYMENT HAVE      "U"   UNKNOW  ERROR ##
			

                              ## Show transaction UNKNOW  ERROR message to the client.

                                  require_once (dirname(__FILE__).'/rdmedia_dragonpay/templates/webReturn_tpl_unknow.php');
                                 
                                  # echo $this->unknow_tpl; #this can also work if this variable is definited in the .xml file & in above class variables


		

                    ## Removing the variables session, it's not needed anymore.
		    $session = JFactory::getSession();
		    $session->clear($this->ordercode);
		    $session->clear('ordercode');
		    $session->clear('coupon');





                   }elseif($dragonpay_webReturn_status == 'R'){
			
			## THE PAYMENT HAVE      "R"   REFUND   STATUS ##

                               ## Show transaction REFUND message to the client.
 
                                    require_once (dirname(__FILE__).'/rdmedia_dragonpay/templates/webReturn_tpl_refund.php');

			            # echo $this->refund_tpl; #this can also work if this variable is definited in the .xml file & in above class variables
			
		 



                    ## Removing the variables session, it's not needed anymore.
		    $session = JFactory::getSession();
		    $session->clear($this->ordercode);
		    $session->clear('ordercode');
		    $session->clear('coupon');




                    }elseif($dragonpay_webReturn_status == 'K'){
			
			## THE PAYMENT HAVE      "K"   CHARGEBACK  STATUS ##
                               
                               ## Show transaction CHARGEBACK message to the client.
                            
                                    require_once (dirname(__FILE__).'/rdmedia_dragonpay/templates/webReturn_tpl_chargeback.php');

                                    # echo $this->chargeback_tpl; #this can also work if this variable is definited in the .xml file & in above class variables
			
			
		

                    ## Removing the variables session, it's not needed anymore.
		    $session = JFactory::getSession();
		    $session->clear($this->ordercode);
		    $session->clear('ordercode');
		    $session->clear('coupon');
                  





                    }elseif($dragonpay_webReturn_status == 'V'){
			
			## THE PAYMENT HAVE      "V"   VOID   STATUS ##	

                                ## Show transaction VOID message to the client.

                                   require_once (dirname(__FILE__).'/rdmedia_dragonpay/templates/webReturn_tpl_void.php');		
			          
                                   # echo $this->void_tpl; #this can also work if this variable is definited in the .xml file & in above class variables


		
                   
                    ## Removing the variables session, it's not needed anymore.
		    $session = JFactory::getSession();
		    $session->clear($this->ordercode);
		    $session->clear('ordercode');
		    $session->clear('coupon');





                      }elseif($dragonpay_webReturn_status == 'A'){
			
			## THE PAYMENT HAVE      "A"   AUTHORIZED  STATUS ##


                                 ## Show transaction AUTHORIZED message to the client.
                                    
                                   require_once (dirname(__FILE__).'/rdmedia_dragonpay/templates/webReturn_tpl_authorized.php');		 
                                       
                                   # echo $this->authorized_tpl; #this can also work if this variable is definited in the .xml file & in above class variables
			
			


	        
                    ## Removing the variables session, it's not needed anymore.
		    $session = JFactory::getSession();
		    $session->clear($this->ordercode);
		    $session->clear('ordercode');
		    $session->clear('coupon');





			
		}else{
			
			## UNKNOWN ERROR      NO ERROR CODE OR STATUS GIVEN IN BACK     - DON'T PROCEED ##				
			

			   # No special display


                    ## Removing the variables session, it's not needed anymore.
		    $session = JFactory::getSession();
		    $session->clear($this->ordercode);
		    $session->clear('ordercode');
		    $session->clear('coupon');  

			
			
		} # END:   if transaction is "S" (Success)


   } # END:   if ($dragonpay_webReturn_digest == $true_webReturn_digest)   # Disable this line to test Webreturn different templateS display


} # END:   function dragonpay()




















//// SECTION   POSTBACK (IPN) RECEPTION


## IPN (Instant Payment Notification): Postback data from Dragonpay
	function IPNProcessPayment($data){
	
      		
		foreach ($data as $varname => $varvalue){
		
                     //Protect futur database imput

			$email .= "$varname: $varvalue\n";
			if(function_exists('get_magic_quotes_gpc') and get_magic_quotes_gpc()){
				$varvalue = urlencode(stripslashes($varvalue));
			}
			else {
				$value = urlencode($value);
			}

                }




              $dragonpay_ipn_txnid = "";
              $dragonpay_ipn_refno = "";
              $dragonpay_ipn_status = "";
              $dragonpay_ipn_message = "";
              $dragonpay_ipn_digest = "";
              $dragonpay_ipn_param1 = "";
              $dragonpay_ipn_param2 = "";



              
              ## Assign posted variables to local variables
	      $dragonpay_ipn_txnid = $data['txnid'];
              $dragonpay_ipn_refno = $data['refno'];
              $dragonpay_ipn_status = $data['status'];
              $dragonpay_ipn_message = $data['message'];
              $dragonpay_ipn_digest = $data['digest'];
              $dragonpay_ipn_param1 = $data['param1'];
              $dragonpay_ipn_param2 = $data['param2'];




      /* // Not yet tested in place of above block
           //Assign posted variables to local variables & Protect futur database imput
	      $dragonpay_ipn_txnid = filter_var( $data['txnid'], FILTER_SANITIZE_STRING);
              $dragonpay_ipn_refno = filter_var( $data['refno'], FILTER_SANITIZE_STRING);
              $dragonpay_ipn_status = filter_var( $data['status'], FILTER_SANITIZE_STRING);
              $dragonpay_ipn_message = filter_var( $data['message'], FILTER_SANITIZE_STRING);
              $dragonpay_ipn_digest = filter_var( $data['digest'], FILTER_SANITIZE_STRING);
              $dragonpay_ipn_param1 = filter_var( $data['param1'], FILTER_SANITIZE_STRING);
              $dragonpay_ipn_param2 = filter_var( $data['param2'], FILTER_SANITIZE_STRING);
      */



           



             ## To check later the authenticity of the digest  
               ## Purge old values
               $ipn_digest_str = "";
               $true_ipn_digest = "";
               ## As per Dragonpay requirement, param1 & param2 are not used to create the below digest 
                ## N.B.: strings bellow to calculate the digest are string posted by dragonpay when string included in the webReturn URL are only in an URL format.     
           $ipn_digest_str = $dragonpay_ipn_txnid.':'.$dragonpay_ipn_refno.':'.$dragonpay_ipn_status.':'.$dragonpay_ipn_message.':'.$this->dragonpay_api_password ;
	     
           $true_ipn_digest = sha1($ipn_digest_str, $raw_output = false);   ## to create 40 Char sha1















$notification_parser = $this->notification_parser;







// START:   if notification parser is set to ON
if ($notification_parser == 1){



        


      


      if ($this->debug_on == 1){ 

            ## For debug/check purpose

                    // Create ManilaTime variable
                    function ManilaTime3() {
                    date_default_timezone_set('Asia/Manila');
                    $date = new DateTime();
                    echo $date->format('Y-m-d H:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
                    // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise
                    }
                    ob_start();
                    ManilaTime3();
                    $ManilaTime = ob_get_clean();


           ## writte above variable values in a file for check purpose
                 $dragonpay_ipn_start = $ManilaTime. '  Dragonpay Notification Received   ';
                 $dragonpay_ipn_start_underline = "--------------------------------------------------------------------";
                 $php_start = "<?php";
                 $php_end = "?>";
                 
                  // debug_log.php 
                  // for security we register in .ph file & with php code start & php code end
                  $fp = fopen(dirname(__FILE__).'/debug_log.php', 'a');
                      fwrite($fp, $php_start."\n");    
                      fwrite($fp, $dragonpay_ipn_start."\n"); 
                      fwrite($fp, $dragonpay_ipn_start_underline."\n"); 
                      fwrite($fp, "txnid:   ".$dragonpay_ipn_txnid."\n");
                      fwrite($fp, "refno:   ".$dragonpay_ipn_refno."\n");
                      fwrite($fp, "status:   ".$dragonpay_ipn_status."\n");
                      fwrite($fp, "message:   ".$dragonpay_ipn_message."\n");
                      fwrite($fp, "digest:   ".$dragonpay_ipn_digest."\n"); 
                      fwrite($fp, "param1:   ".$dragonpay_ipn_param1."\n"); 
                      fwrite($fp, "param2:   ".$dragonpay_ipn_param2."\n"); 
                      fwrite($fp, "true_digest:   ".$true_ipn_digest ."\n"); 
                      fwrite($fp, $php_end."\n"); 
                  fclose($fp);
           }













       ## Check the digest of IPN
	 if ($dragonpay_ipn_digest == $true_ipn_digest) {
             


                                     // BY DESIGN TRANSACTION TABLE ONLY RECORD SUCCESS TRANSACTIONS
                    ## if transaction is "S" (Success)
                    if ($dragonpay_ipn_status == 'S'){

   
	
				## Connecting the database
				$db = JFactory::getDBO();
				## Current date for database.
				$trans_date = date("d-m-Y H:i");
		
				## Check that $dragonpay_ipn_refno has not been previously processed
				$sql = 'SELECT COUNT(pid) AS total
						FROM #__ticketmaster_transactions
						WHERE transid = "'.$dragonpay_ipn_refno.'"';
		
				$db->setQuery($sql);
				$results = $db->loadObject();
		
				if($results->total < 1){
		
					## Including required paths to calculator.
					$path_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'helpers'.DS.'get.amount.php';
					include_once( $path_include );
		
					
					

                
                                    ## find userid
                                 
                                        $db = JFactory::getDBO();   # this line is optional, it's can work without
                                        $sql = $db->getQuery(true); # this line is optional, it's can work without
                                       
                                        $sql = 'SELECT userid FROM #__ticketmaster_orders WHERE ordercode = "'.$dragonpay_ipn_txnid.'"';
		                       
                                        $db->setQuery($sql);

		                        $userid = $db->loadResult();




		                    
   
                                    ## find user email
                                         
                                        $sql = 'SELECT emailaddress FROM #__ticketmaster_clients WHERE userid = "'.$userid.'"';
		                       
                                        $db->setQuery($sql);

		                        $user_email = $db->loadResult();




                                   

                                    ## Total paid amount for this order                              
                                    $order_total_paid_amount = $dragonpay_ipn_param1; 





	
					
									
					JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'tables');
					$row = JTable::getInstance('transaction', 'Table');
		
					## Pickup All Details and create foo=bar&baz=boom&cow=milk&php=hypertext+processor
					$payment_details = http_build_query($data);
										
									
					## Now store all data in the transactions table
                                         ##  only transaction with status "S" are stored there
					$row->transid 	= $dragonpay_ipn_refno;
					$row->details 	= $payment_details;
					$row->amount 	= $order_total_paid_amount;
									
										
					$row->orderid	   = $dragonpay_ipn_txnid;
                                        $row->userid	   = $userid;
                                        $row->type         = 'Dragonpay';
                                        $row->email_paypal = $user_email;
									
					# Store data
					$row->store();
			
					$query = 'UPDATE #__ticketmaster_orders'
						  . ' SET paid = 1, published = 1'
						  . ' WHERE ordercode = "'.$dragonpay_ipn_txnid.'"';
									
					## Do the query now
					$db->setQuery( $query );
									
					## When query goes wrong.. Show message with error.
					if (!$db->query()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
		
					## OK CHECKS DONE AND WE CAN NOW CREATE THE TICKETS ##
									
					## Include the confirmation class to sent the tickets.
					$path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'createtickets.class.php';
					$override = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'override'.DS.'createtickets.class.php';
			
			
					$query = 'SELECT * FROM #__ticketmaster_orders WHERE ordercode = "'.$dragonpay_ipn_txnid.'"';
									
					## Do the query now
					$db->setQuery($query);
					$ordered_tickets = $db->loadObjectList();
									
					## Loop through the ordered tickets and create them one by one.
					for ($i = 0, $n = count($ordered_tickets); $i < $n; $i++ ){
									
							$row  = $ordered_tickets[$i];
									
							## Check if the override is there.
							if (file_exists($override)) {
								## Yes, now we use it.
								require_once($override);
							} else {
								## No, use the standard
								require_once($path);
							}
		
							if(isset($row->orderid)) {
		
								$creator = new ticketcreator( (int)$row->orderid );
								$creator->doPDF();
		
							}
		
					}
		
					## Ticket have been created now -- let's send them to the customer.
					## Include the confirmation class to sent the tickets.
					$path_include = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'sendonpayment.class.php';
					include_once( $path_include );
										
					## Sending the ticket immediatly to the client.
					$creator = new sendonpayment( $dragonpay_ipn_txnid );
					$creator->send();
		
					## Done!! No need to do any extra as this will be handled by the plugin.
					## Client will receive an email with their tickets.
						
					## You may test the functionality or receive an email by uncommenting this lines below.
					## To run this tests, please make sure you change the email addresses.


            


		                      // admin notification by email for success transaction	 			
					if($this->mail_on_notification == 1) { 
						



$subject = 'Dragonpay_Ticketmaster: Payment received for order:  '.$dragonpay_ipn_txnid ;	

$message = 'Dragonpay TRANSACTION SUCCESS Ref '.$dragonpay_ipn_refno.' for order:  '.$dragonpay_ipn_txnid ;	

$email_sender = 'no-reply@'.$hostname;
                                                       
$mailThis =& JFactory::getMailer();
$mailThis -> setSender($email_sender);
$mailThis -> addRecipient($this->notify_email); // from plugin parameters in joomla interface & advanced tab
$mailThis -> setSubject($subject);
$mailThis -> setBody($message);
$mailThis -> Send(); 
	

					
						
					
					} // END:   if($this->mail_on_notification == 1)






          }  ## END:  if transaction is "S" (Success)














                 ## if transaction is "V" (VOID)
                   }elseif($dragonpay_ipn_status == 'V'){

                       
                                        
                         // Nothing to do




                   ## if transaction is "K" (CHARGEBACK)
                   }elseif($dragonpay_ipn_status == 'K'){


                     // By design we only have history tracking for SUCCESS transaction in transaction table, so we can not know if we ever have received such status for this order, so we can not write this transaction in transaction table, and we can send email ONCE to aware server admin that there was this status for this order. To make sure we only send Email once, this code is only present for INP data received (as Dragonpay only send IPN once) and this code is not appropriate in the cron job (web service) as there will be multi-email send for same status order.



               if($this->mail_on_notification == 1) {



                             // START : Send email to server admin, case CHARGEBACK    
  
      
                                ## Connecting the database
				$db = JFactory::getDBO();
						
				## Check this order exist in ticketmaster orders table
				$sql = 'SELECT COUNT(orderid) AS total
						FROM #__ticketmaster_orders
						WHERE ordercode = "'.$dragonpay_ipn_txnid.'"';
		
				$db->setQuery($sql);
				$results = $db->loadObject();
		
			if($results->total >= 1){

                            		                    
        
$subject = 'Dragonpay_Ticketmaster: Additional handling may be needed for order:  '.$dragonpay_ipn_txnid ;	

$message = 'Dragonpay TRANSACTION CHARGEBACK Ref '.$dragonpay_ipn_refno.' for order:  '.$dragonpay_ipn_txnid ;	

$email_sender = 'no-reply@'.$hostname;
                                                       
$mailThis =& JFactory::getMailer();
$mailThis -> setSender($email_sender);
$mailThis -> addRecipient($this->notify_email); // from plugin parameters in joomla interface & advanced tab
$mailThis -> setSubject($subject);
$mailThis -> setBody($message);
$mailThis -> Send(); 
	
				
			
                       }   // END:   if($results->total < 1)
							


            // END : Send email to server admin, case CHARGEBACK



     }   // END:   if($this->mail_on_notification == 1) 








                  ## if transaction is "R" (REFUND)
                   }elseif($dragonpay_ipn_status == 'R'){

                    
                            // By design we only have history tracking for SUCCESS transaction in transaction table, so we can not know if we ever have received such status for this order, so we can not write this transaction in transaction table, and we can send email ONCE to aware server admin that there was this status for this order. To make sure we only send Email once, this code is only present for INP data received (as Dragonpay only send IPN once) and this code is not appropriate in the cron job (web service) as there will be multi-email send for same status order.



          if($this->mail_on_notification == 1) {

        

// START : Send email to server admin, case REFUND  
  
                
                                ## Connecting the database
				$db = JFactory::getDBO();
						
				## Check this order exist in ticketmaster orders table
				$sql = 'SELECT COUNT(orderid) AS total
						FROM #__ticketmaster_orders
						WHERE ordercode = "'.$dragonpay_ipn_txnid.'"';
		
				$db->setQuery($sql);
				$results = $db->loadObject();
		
			if($results->total >= 1){

                            		                    
        
$subject = 'Dragonpay_Ticketmaster: Additional handling may be needed for order:  '.$dragonpay_ipn_txnid ;	

$message = 'Dragonpay TRANSACTION REFUND Ref '.$dragonpay_ipn_refno.' for order:  '.$dragonpay_ipn_txnid ;	

$email_sender = 'no-reply@'.$hostname;

                                                       
$mailThis =& JFactory::getMailer();
$mailThis -> setSender($email_sender);
$mailThis -> addRecipient($this->notify_email); // from plugin parameters in joomla interface & advanced tab
$mailThis -> setSubject($subject);
$mailThis -> setBody($message);
$mailThis -> Send(); 
	
				
			
                       }   // END:   if($results->total < 1)
							


             // END : Send email to server admin  , case REFUND


        
      }   // END:   if($this->mail_on_notification == 1)






                  ## if transaction is "P" (PENDING)
                  }elseif($dragonpay_ipn_status == 'P'){

      
                                  // nothing to do: wait OTC payment



                  ## if transaction is "F" (FAILURE)
                  }elseif($dragonpay_ipn_status == 'F'){

      
                                  // nothing to do: wait new payment try  



                 ## if transaction is "U" (UNKNOWN)
                   }elseif($dragonpay_ipn_status == 'U'){
       
          
                                 // nothing to do: wait new payment try 



                ## if transaction is "A" (AUTHORIZED)
                   }elseif($dragonpay_ipn_status == 'A'){
       
          
                                 // nothing to do: just wait 

	

		// No transaction status case	
		}else{


                                // Nothing to do


              	} 















              }  ## END :   if ($dragonpay_ipn_digest == $true_ipn_digest) 



         }  ## END  :  if ($notification_parser == 1)



     }  # END  :   IPN : Postback data from Dragonpay


	
  } # END  : class plgRDmediaRDMdragonpay
?>
