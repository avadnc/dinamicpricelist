{include file="header.tpl"}
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
                <td class="liste_titre center"> <input type="text" id="ref" name="ref" placeholder="Buscar Producto">
                </td>
                <td class="liste_titre center"></td>
                <td class="liste_titre center">{$supplier}</td>
                <td class="liste_titre center"></td>
                <td class="liste_titre center"></td>
                <td class="liste_titre center">{$category}</td>
                <td class="liste_titre center"></td>
                <td class="liste_titre center"> <button type="submit" class="liste_titre button_search"
                        id="search_button_update">
                        <span class="fa fa-search"></span>
                    </button></td>
            </tr>
            <tr class="liste_titre">
                <th class="wrapcolumntitle liste_titre center">Codigo/Descripción</th>
                <th class="wrapcolumntitle liste_titre center"></th>
                <th class="wrapcolumntitle liste_titre center">Buscar Por Proveedor</th>
                <th class="wrapcolumntitle liste_titre center"> </th>
                <th class="wrapcolumntitle liste_titre center"></th>
                <th class="wrapcolumntitle liste_titre center">Buscar Por Marca</th>
                <th class="wrapcolumntitle liste_titre center"></th>
                <th class="wrapcolumntitle liste_titre center"></th>
            </tr>

            <tr class="liste_titre">
                <th width="100px">Código</th>
                <th class="left">Descripcion</th>
                <th class="center">Precio Compra</th>
                <th class="center">Proveedor</th>
                <th class="center">Fecha</th>
                <th class="center">Margen</th>
                {foreach from=$currencies item=$currency}
                    <th>Venta {$currency['currency']}</th>
                {/foreach}
            </tr>

            {foreach from=$products item=$i}
                <tr class="oddeven">
                    <td>{$i["ref"]}</td>
                    <td>{$i["label"]}</td>
                    {if $i["price"] neq 0}
                        <td class="center">
                            <input class="maxwidth50" type="text" id="{$i['id']}" value="{$i["price"]}"> <span
                                id="cur{$i['id']}">{$i["supplier"][0]["currency"]}</span>
                        </td>
                    {else}
                        <td></td>
                    {/if}

                    {if $i["supplier"]}
                        <td>
                            <select idprod="{$i['id']}" name="suplist" id="suplist{$i['id']}">
                                {foreach from=$i['supplier'] item=$item }

                                    <option value="{$item['supid']}">{$item['name']}</option>

                                    {* {$item|@var_dump} *}
                                {/foreach}
                            </select>
                        </td>
                        <td>
                            <span id="date{$i["id"]}">{$i["supplier"][0]["modification_date"]}</span>
                        </td>
                        <td class="center">
                            <input class="editmarg maxwidth50" margen="{$i["id"]}" type="text" idprod="{$i["id"]}"
                                id="margin{$i["id"]}" value="{$i["supplier"][0]["profit"]}">%
                        </td>

                        {foreach from=$i["currency"] item=$currency}
                            <td class="center">
                                {foreach from=$currency item=$val key=k }
                                    <input type="text" class="maxwidth50" idprod="{$i['id']}" {$k}="{$i["id"]}" value="{$val}">
                                {/foreach}
                            </td>
                        {/foreach}
                    {else}
                        <td><a href="#" class='butAction' target='_blank'>Agregar Proveedor</a></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    {/if}


                </tr>
            {/foreach}

        </table>
    </form>
</div>
{foreach from=$currencies item=$currency}
    <input type="hidden" id="{$currency['currency']}" value="{$currency['rate']}">
{/foreach}
{include file="footer.tpl"}

<script src="js/script.js" defer></script>
