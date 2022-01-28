<?php
/* Smarty version 3.1.34-dev-7, created on 2022-01-28 01:04:57
  from 'D:\laragon\www\suministros\custom\dinamicpricelist\templates\setup.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.34-dev-7',
  'unifunc' => 'content_61f341396b01b0_98193948',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '8c858f95671d71e33e52502213ee8c4c0652b43e' => 
    array (
      0 => 'D:\\laragon\\www\\suministros\\custom\\dinamicpricelist\\templates\\setup.tpl',
      1 => 1643331881,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_61f341396b01b0_98193948 (Smarty_Internal_Template $_smarty_tpl) {
?><form action="<?php echo $_SERVER['PHP_SELF'];?>
" method="POST">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="token" value="<?php echo $_smarty_tpl->tpl_vars['newToken']->value;?>
">
    <table class="noborder centpercent">
        <tr class="liste_titre">
            <td class="titlefield">Cat.</td>
            <td class="titlefield">Tipo</td>
            <td class="titlefield">Valor</td>
        </tr>

        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['data']->value, 'i');
$_smarty_tpl->tpl_vars['i']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['i']->value) {
$_smarty_tpl->tpl_vars['i']->do_else = false;
?>
            <tr class="oddeven">
                <td><input type="hidden" name="id[]" value="<?php echo $_smarty_tpl->tpl_vars['i']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['i']->value['name'];?>
</td>
                <td><select name="type[]">
                        <?php if ($_smarty_tpl->tpl_vars['i']->value['type'] == NULL) {?><option value="">--Seleccione una Opción--</option><?php }?>
                        <option value="fixed" <?php if ($_smarty_tpl->tpl_vars['i']->value['type'] == "fixed") {?>selected <?php }?>>Fijo</option>
                        <option value="percent" <?php if ($_smarty_tpl->tpl_vars['i']->value['type'] == "percent") {?>selected <?php }?>>Porcentaje</option>
                    </select></td>
                <td><input type="text" name="val[]" value="<?php echo $_smarty_tpl->tpl_vars['i']->value['value'];?>
"></td>
            </tr>
        <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>

    </table>
    <input type="submit" value="Guardar" class="button"> 
    <a class="button" href="<?php echo $_smarty_tpl->tpl_vars['self']->value;?>
?action=updateall">Actualizar Precios</a>
</form>
<?php if ($_smarty_tpl->tpl_vars['msg']->value[0] == "OK") {?>
    <span><?php echo $_smarty_tpl->tpl_vars['msg']->value[1];?>
</span>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['msg']->value[0] == "ERR") {?>
    <span><?php echo $_smarty_tpl->tpl_vars['msg']->value[1];?>
</span>
<?php }
}
}
