<?php defined('SYSPATH') or die('No direct script access.');

/**
* paypal class
*
* @package Open Classifieds
* @subpackage Core
* @category Helper
* @author Chema Garrido <chema@garridodiaz.com>, Slobodan Josifovic <slobodan.josifovic@gmail.com>
* @license GPL v3
*/

class Controller_Payment_Paypal extends Controller{
	

	public function after()
	{

	}
	
	public function action_ipn()
	{

		$this->auto_render = FALSE;

		//START PAYPAL IPN
		//manual checks
		$id_product       = Core::post('item_number');
		$paypal_amount    = Core::post('mc_gross');
		$payer_id         = Core::post('payer_id');

		//retrieve info for the item in DB
		$product = new Model_product();
		$product = $product->where('id_product', '=', $id_product)
					   ->where('status', '=', Model_Product::STATUS_ACTIVE)
					   ->limit(1)->find();
		
		if($product->loaded())
		{
			if (	Core::post('mc_gross')          == number_format($product->price, 2, '.', '')
				&&  Core::post('mc_currency')       == $product->currency
				&& (Core::post('receiver_email')    == core::config('payment.paypal_account') 
					|| Core::post('business')       == core::config('payment.paypal_account')))
			{//same price , currency and email no cheating ;)
                if (paypal::validate_ipn()) 
				{
					//create user if doesnt exists
                         //send email to user with password
                    $user = Model_User::create_email(Core::post('payer_email'),Core::post('first_name').' '.Core::post('last_name'));

                    Model_Order::create_order(NULL,$user,$product,Core::post('txn_id'),'paypal');
                        
				}
				else
				{
					Kohana::$log->add(Log::ERROR, 'A payment has been made but is flagged as INVALID');
					$this->response->body('KO');
				}	
			} 
			else //trying to cheat....
			{
				Kohana::$log->add(Log::ERROR, 'Attempt illegal actions with transaction');
				$this->response->body('KO');
			}
		}// END order loaded
		else
		{
            Kohana::$log->add(Log::ERROR, 'Product not loaded');
            $this->response->body('KO');
		}

		$this->response->body('OK');
	} 

	/**
	 * [action_form] generates the form to pay at paypal
	 */
	public function action_form()
	{ 
		$this->auto_render = FALSE;

        $product_id = $this->request->param('id',0);

        $product = new Model_product();

        $product->where('id_product','=',$product_id)
            ->where('status','=',Model_Product::STATUS_ACTIVE)
            ->limit(1)->find();

    ///testing
        $user = Model_User::create_email('admin2@deambulando.com','chema');

        Model_Order::sale(NULL,$user,$product,time(),'paypal');

        d('sd');

		

        if ($product->loaded())
        {
        	
			$paypal_url = (Core::config('payment.sandbox')) ? Paypal::url_sandbox_gateway : Paypal::url_gateway;

		 	$paypal_data = array('product_id'           => $product_id,
	                             'amount'            	=> number_format($product->price, 2, '.', ''),
	                             'site_name'        	=> core::config('general.site_name'),
	                             'return_url'           => URL::base(TRUE),//@todo return url from config like TOS
	                             'paypal_url'        	=> $paypal_url,
	                             'paypal_account'    	=> core::config('payment.paypal_account'),
	                             'paypal_currency'    	=> $product->currency,
	                             'item_name'			=> $product->title);
			
			$this->template = View::factory('paypal', $paypal_data);
            $this->response->body($this->template->render());
			
		}
		else
		{
			Alert::set(Alert::INFO, __('Product could not be loaded'));
            $this->request->redirect(Route::url('default'));
		}
	}

}