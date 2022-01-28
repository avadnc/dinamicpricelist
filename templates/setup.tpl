<form action="{$smarty.server.PHP_SELF}" method="POST">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="token" value="{$newToken}">
    <table class="noborder centpercent">
        <tr class="liste_titre">
            <td class="titlefield">Cat.</td>
            <td class="titlefield">Tipo</td>
            <td class="titlefield">Valor</td>
        </tr>

        {foreach from=$data item=$i}
            <tr class="oddeven">
                <td><input type="hidden" name="id[]" value="{$i.id}">{$i.name}</td>
                <td><select name="type[]">
                        {if $i.type eq NULL}<option value="">--Seleccione una Opción--</option>{/if}
                        <option value="fixed" {if $i.type eq "fixed"}selected {/if}>Fijo</option>
                        <option value="percent" {if $i.type eq "percent"}selected {/if}>Porcentaje</option>
                    </select></td>
                <td><input type="text" name="val[]" value="{$i.value}"></td>
            </tr>
        {/foreach}

    </table>
    <input type="submit" value="Guardar" class="button"> 
    <a class="button" href="{$self}?action=updateall">Actualizar Precios</a>
</form>
{if $msg[0] eq "OK"}
    <span>{$msg[1]}</span>
{/if}

{if $msg[0] eq "ERR"}
    <span>{$msg[1]}</span>
{/if}
