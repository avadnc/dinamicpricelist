{include file="header.tpl"}
{php}
global $langs;
{/php}
<style>
    #loading {
        position: fixed;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0.7;
        background-color: #fff;
        z-index: 99;
    }

    #loading-image {
        z-index: 100;
    }
</style>

<div id="loading">
    <img id="loading-image" src="img/spin.gif" alt="Loading..." />
</div>
<div class="liste_titre liste_titre_bydiv centpercent"></div>
<div class="div-table-responsive">

    <form id="buscar" action="{$action}" method="POST">
        <input type="hidden" name="action" value="getproducts">
        <input type="hidden" name="token" value="{$newToken}">
        <table class="tagtable liste listwithfilterbefore">
            <tr class="liste_titre_filter">
                <td class="liste_titre center"> <input type="text" id="ref" name="ref"
                        placeholder="{php}echo $langs->trans('search_product'); {/php}">
                </td>
                <td class="liste_titre center">{$supplier}</td>
                <td class="liste_titre center">{$category}</td>
                <td class="liste_titre center"><button type="submit" class="liste_titre button_search"
                        id="search_button_update"><span class="fa fa-search"></span></button>
                </td>
            </tr>
            <tr class="liste_titre">
                <th class="wrapcolumntitle liste_titre center">{php}echo $langs->trans('search_code_description');
                    {/php}</th>
                <th class="wrapcolumntitle liste_titre center">{php}echo $langs->trans('search_supplier'); {/php}</th>
                <th class="wrapcolumntitle liste_titre center">{php}echo $langs->trans('search_category'); {/php}</th>
                <th class="wrapcolumntitle liste_titre center"></th>
            </tr>
        </table>
    </form>

</div>

<div class="div-table-responsive">

    <table id="tablaupdate" class="tagtable liste listwithfilterbefore">
        <thead>
            <tr class="liste_titre">
                <th>{php}echo $langs->trans('code'); {/php}</th>
                <th>{php}echo $langs->trans('description'); {/php}</th>
                <th>{php}echo $langs->trans('buy_price'); {/php}</th>
                <th>{php}echo $langs->trans('supplier'); {/php}</th>
                <th>{php}echo $langs->trans('date'); {/php}</th>
                <th>{php}echo $langs->trans('margin'); {/php}</th>
                {if isset($currencies)}
                    {foreach from=$currencies item=$currency}
                        <th>{php}echo $langs->trans('sell_price'); {/php} {$currency['currency']}</th>
                    {/foreach}
                {else}
                    <th>{php}echo $langs->trans('sell_price'); {/php}</th>
                {/if}
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
{if isset($currencies)}
    {foreach from=$currencies item=$currency}
        <input type="hidden" name="currency[]" id="{$currency['currency']}" value="{$currency['rate']}">
    {/foreach}
{else}
    <input type="hidden" name="currency[]" id="{$localcurrency}" value="1">
{/if}
<input type="hidden" id="localcurrency" value="{$localcurrency}">
{include file="footer.tpl"}

<script src="js/script.js"></script>
