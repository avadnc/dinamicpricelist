<?php
/* Smarty version 3.1.34-dev-7, created on 2021-10-06 23:44:53
  from 'D:\laragon\www\suministros\custom\dinamicpricelist\templates\pricelist.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.34-dev-7',
  'unifunc' => 'content_615e34f5a8df81_59786894',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '5398265f415a81bd9a43052d2951994f1a5fe4d2' => 
    array (
      0 => 'D:\\laragon\\www\\suministros\\custom\\dinamicpricelist\\templates\\pricelist.tpl',
      1 => 1633129535,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:header.tpl' => 1,
    'file:footer.tpl' => 1,
  ),
),false)) {
function content_615e34f5a8df81_59786894 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:header.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
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

    <form id="buscar" action="<?php echo $_smarty_tpl->tpl_vars['action']->value;?>
" method="POST">
        <input type="hidden" name="action" value="getproducts">
        <input type="hidden" name="token" value="<?php echo $_smarty_tpl->tpl_vars['newToken']->value;?>
">
        <table class="tagtable liste listwithfilterbefore">
            <tr class="liste_titre_filter">
                <td class="liste_titre center"> <input type="text" id="ref" name="ref" placeholder="Buscar Producto">
                </td>
                <td class="liste_titre center"><?php echo $_smarty_tpl->tpl_vars['supplier']->value;?>
</td>
                <td class="liste_titre center"><?php echo $_smarty_tpl->tpl_vars['category']->value;?>
</td>
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
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['currencies']->value, 'currency');
$_smarty_tpl->tpl_vars['currency']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['currency']->value) {
$_smarty_tpl->tpl_vars['currency']->do_else = false;
?>
                    <th><?php echo $_smarty_tpl->tpl_vars['currency']->value['currency'];?>
</th>
                <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                <th>Fecha Modificación</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>

<?php $_smarty_tpl->_subTemplateRender("file:footer.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php echo '<script'; ?>
 src="js/script.js" defer><?php echo '</script'; ?>
>
<?php }
}
