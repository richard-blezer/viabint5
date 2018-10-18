Plugin: vt_special_sale_assortments                                       v1.1.0

[#### BESCHREIBUNG ####]

    Das Plugin ermöglicht es, Bundle Gruppen im Admin anzulegen und Produkte zuzuweisen.
    Im Shop Frontend im Warenkorb werden dann Bundle Artikel zu einem Bundle zusammen
    gefasst, wenn die angegebene Bundle Gruppen Größe erreicht ist. 

    Beispiel:
    Sie verkaufen DVDs zu je 10,- EUR und möchten dem Kunden für eine Auswahl an Produkten
    3 DVDs zu 25,- EUR anbieten. 

    Dann erstellen Sie im Admin eine Bundle Gruppe und geben die Größe des Bundles an. 
    Sie weisen der Bundle Gruppe beliebige Artikel zu und geben den Preis für das Bundle,
    Kundengruppen abhängig, an. Fertig. Den Rest macht der Warenkorb.

    Der Shop erkennt wenn genügend Artikel für ein Bundle im Warenkorb liegen und 
    fasst diese zu einem Bundle zusammen.



[#### TEMPLATE ANPASSUNG ####]

    Link zur Übersichtsseite der Bundles
    Code kann in beliebige Template Datei an beliebiger Stelle eingebunden werden:
    
        Bsp 1:
        <a href="{link page=$smarty.const.VT_SPECIAL_SALE_ASSORTMENTS_PAGE_SEO}">{txt key=TEXT_VT_SSA_SHOW_GROUPS}</a>

        Bsp 2:
        <a href="{link page=$smarty.const.VT_SPECIAL_SALE_ASSORTMENTS_PAGE_SEO}">{button text=TEXT_VT_SSA_SHOW_GROUPS file='button_vt_ssa_back_to_groups.gif'}</a>



    Code kann in folgender Datei an beliebiger Stelle eingebunden werden:

    /templates/IHR_TEMPLATE/index.html

        {box name=vt_special_sale_assortments type=user}



    Code kann in folgender Datei an beliebiger Stelle eingebunden werden:

    /templates/IHR_TEMPLATE/xtCore/pages/product/product.html

        {if $special_sale_group}    
            {if $special_sale_group.ssag_name}{$special_sale_group.ssag_name}<br />{/if}
            {if $special_sale_group.ssag_short_desc}{$special_sale_group.ssag_short_desc}<br />{/if}
            {if $special_sale_group.ssag_image}{img img=$special_sale_group.ssag_image type=m_thumb alt=$special_sale_group.ssag_name}{/if}
        {/if}



    Code kann in folgender Datei an beliebiger Stelle eingebunden werden:

    /templates/IHR_TEMPLATE/xtCore/pages/product_listing/product_listing_v1.html

        {if $module_data.special_sale_group}    
            {if $module_data.special_sale_group.ssag_name}{$module_data.special_sale_group.ssag_name}<br />{/if}
            {if $module_data.special_sale_group.ssag_short_desc}{$module_data.special_sale_group.ssag_short_desc}<br />{/if}
            {if $module_data.special_sale_group.ssag_image}{img img=$module_data.special_sale_group.ssag_image type=m_thumb alt=$module_data.special_sale_group.ssag_name}{/if}
        {/if}




    Der nachfolgende Code muss in der nachfolgenden Datei ersetzt werden:

    > Diese Änderung ist nur nötig wenn Sie die Template Änderungen die unter dem Punkt [#### Hinweis ####] stehen
    > nicht vornehmen anderen Falls benötigen Sie diese Änderung nicht


    /templates/IHR_TEMPLATE/xtCore/pages/cart.html

        Suchen nach:
            {form type=text name=qty[] value=$data.products_quantity style='width:30px;'}


        Ersetzen durch:
            {if $data.ssag_bundle}
                {form type=text name=qty[] value=$data.products_quantity style='width:30px; background:#CCCCCC;' params="readonly='true'"}
            {else}
                {form type=text name=qty[] value=$data.products_quantity style='width:30px;'}
            {/if}



[#### HINWEIS ####]

    Der nachfolgende Code ist beispielhaft beigefügt und kann in die folgenden Templates eingebunden werden 
    (erstellt für xt:Commerce Veyton v4.0.13 - "xt_default" - Template )

    Zum einbinden in ein anderes Template als das "xt_default" - Template sind ggf. Anpassungen an den
    unten angebenen, zu ersetzenden, Code Zeilen notwendig.  

    Die bereits geänderten Dateien für das "xt_default" - Template finden Sie auch im Ordner "README" des Plugins.

    Diese Anpassungen ermöglichen die Gruppierte Darstellung der Bundle Produkte im Warenkorb, Checkout, Kundenkonto 
    und in den Mails. 

    Vorschaubilder liegen dem Plugin im Ordner "Images" bei.    



    Der nachfolgende Code muss in der nachfolgenden Datei ersetzt werden:
    
    /templates/IHR_TEMPLATE/xtCore/pages/cart.html

        Suchen nach:

            {foreach name=aussen item=data from=$cart_data}
            <tr class="{cycle values="contentrow1,contentrow2"}">
                <td class="left">{form type=text name=qty[] value=$data.products_quantity style='width:30px;'}</td>
                <td class="left">
                            <strong><a href="{$data.products_link}">{$data.products_name}</a></strong>
                            {if $data.shipping_status}<br /><p class="shippingtime">{txt key=TEXT_SHIPPING_STATUS}&nbsp;{$data.shipping_status}</p>{/if}
                <td class="right">{$data.products_price.formated}</td>
                <td class="right">{$data.products_final_price.formated}</td>
                <td class="right">
                    {form type=hidden name=products_key[] value=$data.products_key}
                    {form type=checkbox name=cart_delete[] value=$data.products_key}</td>
            </tr>
            {$data.products_information}
            {/foreach}

        Ersetzen durch:

            {*Dies ist zum Anzeigen der Bundles*} 
            {assign var=ssag_bundle_head value=''}
            {foreach name=aussen item=data from=$cart_data}
                {if $data.ssag_bundle_id && $ssag_bundle_head != $data.ssag_bundle_id}
                    {assign var=ssag_bundle_head value=$data.ssag_bundle_id}
                    {cycle assign=ssag_bundle_head_row name="bundle_cart" values="contentrow1,contentrow2" print=false}
                    <tr class="{$ssag_bundle_head_row}">
                        <td class="left">
                            {form type=text name=bla value=1 style='width:30px; background:#CCCCCC;' params="readonly='true'"}
                        </td>
                        <td class="left" width="30%">
                            <strong>{$data.ssa_group.ssag_name} {$data.ssag_bundle}</strong>
                        </td>
                        <td class="right">{$data.ssa_group.ssap_price.formated}</td>
                        <td class="right">{$data.ssa_group.ssap_price.formated}</td>
                        <td class="right"></td>
                    </tr>
                    {if $data.ssa_group.ssag_short_desc}
                    <tr class="{$ssag_bundle_head_row}">
                        <td> </td>
                        {*if $data.ssa_group.ssag_image}
                        <td>{img img=$data.ssa_group.ssag_image type=m_thumb alt=$data.ssa_group.ssag_name}</td>
                        <td class="left" colspan="2" valign="top">
                        {else*}    
                        <td class="left" colspan="3">
                        {*/if*}                
                            {$data.ssa_group.ssag_short_desc}
                        </td> 
                        <td></td>
                    </tr>
                    {/if}
                {/if}
                {if $data.ssag_bundle_id && $ssag_bundle_head == $data.ssag_bundle_id}
                    <tr class="{$ssag_bundle_head_row}">
                        <td class="left">
                            {form type=hidden name=qty[] value=$data.products_quantity style='width:30px; background:#CCCCCC;' params="readonly='true'"}
                        </td>
                        <td class="left">
                                    <strong><a href="{$data.products_link}">{$data.products_name}</a></strong>
                                    {if $data.shipping_status}<br /><p class="shippingtime">{txt key=TEXT_SHIPPING_STATUS}&nbsp;{$data.shipping_status}</p>{/if}
                        <td class="right">{*$data.products_price.formated*}</td>
                        <td class="right">{*$data.products_final_price.formated*}</td>
                        <td class="right">{form type=hidden name=products_key[] value=$data.products_key}{form type=checkbox name=cart_delete[] value=$data.products_key}</td>
                    </tr>        
                {/if}
            {/foreach}


            {*Dies ist zum Anzeigen der Normalen Artikel*}
            {foreach name=aussen item=data from=$cart_data}
                {if !$data.ssag_bundle}
                    <tr class="{if $ssag_bundle_head_row == 'contentrow1'}{cycle values="contentrow2,contentrow1"}{else}{cycle values="contentrow1,contentrow2"}{/if}">
                    <td class="left">
                            {form type=text name=qty[] value=$data.products_quantity style='width:30px;'}
                    </td>
                    <td class="left">
                                <strong><a href="{$data.products_link}">{$data.products_name}</a></strong>
                                {if $data.shipping_status}<br /><p class="shippingtime">{txt key=TEXT_SHIPPING_STATUS}&nbsp;{$data.shipping_status}</p>{/if}
                    <td class="right">{$data.products_price.formated}</td>
                    <td class="right">{$data.products_final_price.formated}</td>
                    <td class="right">{form type=hidden name=products_key[] value=$data.products_key}{form type=checkbox name=cart_delete[] value=$data.products_key}</td>
                    </tr>
                    {$data.products_information}
                {/if}
            {/foreach}



    Der nachfolgende Code muss in der nachfolgenden Datei ersetzt werden:
    
    /templates/IHR_TEMPLATE/xtCore/boxes/box_cart.html

        Suchen nach:

            {foreach name=aussen item=data from=$cart_data}
                    <p>{$data.products_quantity}&nbsp;x&nbsp;<a href="{$data.products_link}">{$data.products_name|truncate:20:"...":true}</a></p>
            {/foreach}

        Ersetzen durch:

            {assign var=ssag_bundle_head value=''}
            {foreach name=aussen item=data from=$cart_data}

                {if $data.ssag_bundle_id && $ssag_bundle_head != $data.ssag_bundle_id}
                    {assign var=ssag_bundle_head value=$data.ssag_bundle_id}
                    <p>1&nbsp;x&nbsp;<a href="{$data.ssa_group.ssag_link}">{$data.ssa_group.ssag_name|truncate:20:"...":true} {$data.ssag_bundle}</a></p>
                {elseif !$data.ssag_bundle_id}
                    <p>{$data.products_quantity}&nbsp;x&nbsp;<a href="{$data.products_link}">{$data.products_name|truncate:20:"...":true}</a></p>
                {/if}
            {/foreach}



    Der nachfolgende Code muss in der nachfolgenden Datei ersetzt werden:
    
    /templates/IHR_TEMPLATE/xtCore/pages/checkout/subpage_confirmation.html

        Suchen nach:

            {foreach name=aussen item=data from=$data}
                <tr class="{cycle values="contentrow1,contentrow2"}">
                    <td class="left">{$data.products_quantity}</td>
                    <td class="left">
                                <strong><a href="{$data.products_link}">{$data.products_name}</a></strong>
                                {if $data.shipping_status}<br /><p class="shippingtime">{txt key=TEXT_SHIPPING_STATUS}&nbsp;{$data.shipping_status}</p>{/if}
                    <td class="left">
                    {if $data._cart_discount}
                    <span class="old-price">{$data._original_products_price.formated}</span>
                    {$data.products_price.formated} (-{$data._cart_discount} %)
                    {else}
                    {$data.products_price.formated}
                    {/if}
                    </td>
                    <td class="right">{$data.products_final_price.formated}</td>
                </tr>
                {$data.products_information} 
            {/foreach}

        Ersetzen durch:

            {*Dies ist zum Anzeigen der Bundles*} 
            {assign var=ssag_bundle_head value=''}{assign var=checkout_data value=$data}
            {foreach name=aussen item=data from=$checkout_data}
                {if $data.ssag_bundle_id && $ssag_bundle_head != $data.ssag_bundle_id}
                    {assign var=ssag_bundle_head value=$data.ssag_bundle_id}
                    {cycle assign=ssag_bundle_head_row name="bundle_cart" values="contentrow1,contentrow2" print=false}
                    <tr class="{$ssag_bundle_head_row}">
                        <td class="left">1</td>
                        <td class="left">
                            <strong>{$data.ssa_group.ssag_name} {$data.ssag_bundle}</strong>
                        </td>
                        <td class="left">{$data.ssa_group.ssap_price.formated}</td>
                        <td class="right">{$data.ssa_group.ssap_price.formated}</td>
                    </tr>
                    {if $data.ssa_group.ssag_short_desc}
                    <tr class="{$ssag_bundle_head_row}">
                        <td> </td>
                        {*if $data.ssa_group.ssag_image}
                        <td>{img img=$data.ssa_group.ssag_image type=m_thumb alt=$data.ssa_group.ssag_name}</td>
                        <td class="left" valign="top">
                        {else*}    
                        <td class="left" colspan="2">
                        {*/if*}                
                            {$data.ssa_group.ssag_short_desc}
                        </td> 
                        <td></td>
                    </tr>
                    {/if}
                {/if}
                {if $data.ssag_bundle_id && $ssag_bundle_head == $data.ssag_bundle_id}
                    <tr class="{$ssag_bundle_head_row}">
                        <td class="left">{*$data.products_quantity*}</td>
                        <td class="left">
                                    <strong><a href="{$data.products_link}">{$data.products_name}</a></strong>
                                    {*if $data.shipping_status}<br /><p class="shippingtime">{txt key=TEXT_SHIPPING_STATUS}&nbsp;{$data.shipping_status}</p>{/if*}
                        <td class="left">
                        {*if $data._cart_discount}
                        <span class="old-price">{$data._original_products_price.formated}</span>
                        {$data.products_price.formated} (-{$data._cart_discount} %)
                        {else}
                        {$data.products_price.formated}
                        {/if*}
                        </td>
                        <td class="right">{*$data.products_final_price.formated*}</td>
                    </tr>        
                {/if}
            {/foreach}


            {*Dies ist zum Anzeigen der Normalen Artikel*}
            {foreach name=aussen item=data from=$checkout_data}
                {if !$data.ssag_bundle}
                    <tr class="{if $ssag_bundle_head_row == 'contentrow1'}{cycle values="contentrow2,contentrow1"}{else}{cycle values="contentrow1,contentrow2"}{/if}">
                        <td class="left">{$data.products_quantity}</td>
                        <td class="left">
                                    <strong><a href="{$data.products_link}">{$data.products_name}</a></strong>
                                    {if $data.shipping_status}<br /><p class="shippingtime">{txt key=TEXT_SHIPPING_STATUS}&nbsp;{$data.shipping_status}</p>{/if}
                        <td class="left">
                        {if $data._cart_discount}
                        <span class="old-price">{$data._original_products_price.formated}</span>
                        {$data.products_price.formated} (-{$data._cart_discount} %)
                        {else}
                        {$data.products_price.formated}
                        {/if}
                        </td>
                        <td class="right">{$data.products_final_price.formated}</td>
                    </tr>
                    {$data.products_information}
                {/if}
            {/foreach}  




    Der nachfolgende Code muss in der nachfolgenden Datei ersetzt werden:
    
    /templates/IHR_TEMPLATE/xtCore/pages/account_history_info.html

        Suchen nach:

            {foreach name=aussen item=order_values from=$order_products}
                <tr class="{cycle values="contentrow1,contentrow2"}">
                    <td class="left">{$order_values.products_quantity}&nbsp;x</td>
                    <td class="left"><strong>{$order_values.products_name}</strong></td>
                    <td class="left">{$order_values.products_model}</td>
                    <td class="right">{$order_values.products_price.formated}</td>
                    <td class="right">{$order_values.products_final_price.formated}</td>
                </tr>
                {$order_values.products_information.content}
            {/foreach}

        Ersetzen durch:

            {*Dies ist zum Anzeigen der Bundles*} 
            {assign var=ssag_bundle_head value=''}
            {foreach name=aussen item=order_values from=$order_products}
                {if $order_values.ssag_bundle_id && $ssag_bundle_head != $order_values.ssag_bundle_id}
                    {assign var=ssag_bundle_head value=$order_values.ssag_bundle_id}
                    {cycle assign=ssag_bundle_head_row name="bundle_cart" values="contentrow1,contentrow2" print=false}
                    <tr class="{$ssag_bundle_head_row}">
                        <td class="left">
                            1.00&nbsp;x
                        </td>
                        <td class="left"><strong>{$order_values.ssa_group.ssag_name} {$order_values.ssag_bundle}</strong></td>
                        <td class="right"></td>            
                        <td class="right">{$order_values.ssa_group.ssap_price.formated}</td>
                        <td class="right">{$order_values.ssa_group.ssap_price.formated}</td>
                    </tr>
                    {if $order_values.ssa_group.ssag_short_desc}
                    <tr class="{$ssag_bundle_head_row}">
                        <td> </td>
                        {*if $data.ssa_group.ssag_image}
                        <td>{img img=$order_values.ssa_group.ssag_image type=m_thumb alt=$order_values.ssa_group.ssag_name}</td>
                        <td class="left" colspan="2" valign="top">
                        {else*}    
                        <td class="left" colspan="3">
                        {*/if*}                
                            {$order_values.ssa_group.ssag_short_desc}
                        </td> 
                        <td></td>
                    </tr>
                    {/if}
                {/if}
                {if $order_values.ssag_bundle_id && $ssag_bundle_head == $order_values.ssag_bundle_id}
                    <tr class="{$ssag_bundle_head_row}">
                        <td class="left"></td>
                        <td class="left"><strong>{$order_values.products_name}</strong>
                        <td class="right">{$order_values.products_model}</td>                        
                        <td class="right"></td>
                        <td class="right"></td>
                    </tr>        
                {/if}
            {/foreach}


            {*Dies ist zum Anzeigen der Normalen Artikel*}
            {foreach name=aussen item=order_values from=$order_products}
                {if !$order_values.ssag_bundle}
                    <tr class="{if $ssag_bundle_head_row == 'contentrow1'}{cycle values="contentrow2,contentrow1"}{else}{cycle values="contentrow1,contentrow2"}{/if}">
                        <td class="left">{$order_values.products_quantity}&nbsp;x</td>
                        <td class="left"><strong>{$order_values.products_name}</strong></td>
                        <td class="left">{$order_values.products_model}</td>
                        <td class="right">{$order_values.products_price.formated}</td>
                        <td class="right">{$order_values.products_final_price.formated}</td>
                    </tr>
                    {$order_values.products_information.content}
                {/if}
            {/foreach} 



    Der nachfolgende Code muss in der nachfolgenden Datei ersetzt werden:
    
    ADMIN/Inhalte/Email-Manager/send_order > HTML-Mail 

        Suchen nach:

            {foreach name=aussen item=order_values from=$order_products}
                <tr class="{cycle values="contentrow1,contentrow2"}">
                        <td class="left">{$order_values.products_quantity} x</td>
                        <td class="left">{$order_values.products_name}</strong></td>
                        <td class="left">{$order_values.products_model}</td>
                        <td class="right">{$order_values.products_price.formated}</td>
                        <td class="right">{$order_values.products_final_price.formated}</td>
                </tr>
                {$order_values.products_information.html_content}
            {/foreach}

        Ersetzen durch:

            {*Dies ist zum Anzeigen der Bundles*} 
            {assign var=ssag_bundle_head value=''}
            {foreach name=aussen item=order_values from=$order_products}
                {if $order_values.ssag_bundle_id && $ssag_bundle_head != $order_values.ssag_bundle_id}
                    {assign var=ssag_bundle_head value=$order_values.ssag_bundle_id}
                    {cycle assign=ssag_bundle_head_row name="bundle_cart" values="contentrow1,contentrow2" print=false}
                    <tr class="{$ssag_bundle_head_row}">
                        <td class="left">
                            1.00&nbsp;x
                        </td>
                        <td class="left"><strong>{$order_values.ssa_group.ssag_name} {$order_values.ssag_bundle}</strong></td>
                        <td class="right"></td>            
                        <td class="right">{$order_values.ssa_group.ssap_price.formated}</td>
                        <td class="right">{$order_values.ssa_group.ssap_price.formated}</td>
                    </tr>
                    {if $order_values.ssa_group.ssag_short_desc}
                    <tr class="{$ssag_bundle_head_row}">
                        <td> </td>
                        {*if $data.ssa_group.ssag_image}
                        <td>{img img=$order_values.ssa_group.ssag_image type=m_thumb alt=$order_values.ssa_group.ssag_name}</td>
                        <td class="left" colspan="2" valign="top">
                        {else*}    
                        <td class="left" colspan="3">
                        {*/if*}                
                            {$order_values.ssa_group.ssag_short_desc}
                        </td> 
                        <td></td>
                    </tr>
                    {/if}
                {/if}
                {if $order_values.ssag_bundle_id && $ssag_bundle_head == $order_values.ssag_bundle_id}
                    <tr class="{$ssag_bundle_head_row}">
                        <td class="left"></td>
                        <td class="left">{$order_values.products_name}
                        <td class="right">{$order_values.products_model}</td>                        
                        <td class="right"></td>
                        <td class="right"></td>
                    </tr>        
                {/if}
            {/foreach}


            {*Dies ist zum Anzeigen der Normalen Artikel*}
            {foreach name=aussen item=order_values from=$order_products}
                {if !$order_values.ssag_bundle}
                    <tr class="{if $ssag_bundle_head_row == 'contentrow1'}{cycle values="contentrow2,contentrow1"}{else}{cycle values="contentrow1,contentrow2"}{/if}">
                        <td class="left">{$order_values.products_quantity}&nbsp;x</td>
                        <td class="left"><strong>{$order_values.products_name}</strong></td>
                        <td class="left">{$order_values.products_model}</td>
                        <td class="right">{$order_values.products_price.formated}</td>
                        <td class="right">{$order_values.products_final_price.formated}</td>
                    </tr>
                    {$order_values.products_information.content}
                {/if}
            {/foreach} 



    Der nachfolgende Code muss in der nachfolgenden Datei ersetzt werden:
    
    ADMIN/Inhalte/Email-Manager/send_order > HTML-Mail 

        Suchen nach:

            {foreach name=aussen item=order_values from=$order_products}
            {$order_values.products_quantity} x {$order_values.products_name} = {$order_values.products_final_price.formated}
            {/foreach}

        Ersetzen durch:

            {*Dies ist zum Anzeigen der Bundles*} 
            {assign var=ssag_bundle_head value=''}
            {foreach name=aussen item=order_values from=$order_products}
                {if $order_values.ssag_bundle_id && $ssag_bundle_head != $order_values.ssag_bundle_id}
                    {assign var=ssag_bundle_head value=$order_values.ssag_bundle_id}
                            1.00 x {$order_values.ssa_group.ssag_name} {$order_values.ssag_bundle} = {$order_values.ssa_group.ssap_price.formated}
                    {if $order_values.ssa_group.ssag_short_desc}
                            {$order_values.ssa_group.ssag_short_desc}
                    {/if}
                {/if}
                {if $order_values.ssag_bundle_id && $ssag_bundle_head == $order_values.ssag_bundle_id}
                    {$order_values.products_name} 
                {/if}
            {/foreach}


            {*Dies ist zum Anzeigen der Normalen Artikel*}
            {foreach name=aussen item=order_values from=$order_products}
                {if !$order_values.ssag_bundle}
            {$order_values.products_quantity} x {$order_values.products_name} = {$order_values.products_final_price.formated}
            {$order_values.products_information.txt_content}
                {/if}
            {/foreach} 
