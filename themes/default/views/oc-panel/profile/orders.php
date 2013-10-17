<?php defined('SYSPATH') or die('No direct script access.');?>


<div class="page-header">
	<h1><?=__('Purchases')?></h1>
    
</div>


<table class="table table-striped">
    <thead>
         <tr>
            <th>#</th>
            <th><?=__('Product')?></th>
            <th><?=__('Date')?></th>
            <th><?=__('Support until')?></th>
            <th><?=__('Price')?></th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?foreach ($orders as $order):?>
        <tr class="info">
            <td><?=$order->id_order;?></td>
            <td><?=$order->product->title?></td>
            <td><?=$order->pay_date;?></td>
            <td><?=$order->support_date;?></td>
            <td><?=$order->amount.' '.$order->currency;?></td>
            <td><a title="<?=__('Download')?>" href="<?=Route::url('oc-panel',array('controller'=>'profile','action'=>'download','id'=>$order->id_order))?>" class="btn btn-warning"><i class="icon-download icon-white"></i></a></td>
        </tr>
        <tr>
            <td colspan="5">
                <table class="table table-striped">
                    <th><?=__('License')?></th>
                    <th><?=__('Created')?></th>
                    <th><?=__('Domain')?></th>
                <?foreach ($licenses as $license):?>
                    <?if($license->id_order == $order->id_order):?>
                    <tr>
                        <td><?=$license->license?></td>
                        <td><?=$license->created?></td>
                        <td><?=($license->status==Model_License::STATUS_NOACTIVE)?__('Inactive'):$license->domain?></td>
                    <tr>
                    <?endif?>
                <?endforeach?>
                </table>
            </td>
        </tr>
        <?endforeach?>
    </tbody>

</table>