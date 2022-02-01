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
                <td class="liste_titre center">{$supplier}</td>
                <td class="liste_titre center">{$category}</td>
                <td class="liste_titre center"><button type="submit" class="liste_titre button_search"
                        id="search_button"><span class="fa fa-search"></span></button>
                </td>
            </tr>
            <tr class="liste_titre">
                <th class="wrapcolumntitle liste_titre center">Codigo/Descripción</th>
                <th class="wrapcolumntitle liste_titre center"> Buscar Por Proveedor</th>
                <th class="wrapcolumntitle liste_titre center"> Buscar Por Marca</th>
                <th class="wrapcolumntitle liste_titre center"></th>
            </tr>
        </table>
    </form>
</div>

<div class="div-table-responsive">

    <table id="tabla" class="tagtable liste listwithfilterbefore">
        <thead>
            <tr class="liste_titre">
                <th>Código</th>
                <th>Sustituto</th>
                <th>Descripción</th>
                <th>Stock</th>
                {if isset($currencies)}
                    {foreach from=$currencies item=$currency}
                        <th>Venta {$currency['currency']}</th>
                    {/foreach}
                {else}
                    <th>Precio de Venta</th>
                {/if}
                <th>Fecha Modificación</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>

{include file="footer.tpl"}

<script src="js/script.js" defer></script>
