{php}
global $langs;
{/php}
<form action="{$smarty.server.PHP_SELF}" method="POST">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="token" value="{$newToken}">
    <table class="noborder centpercent">
        <tr class="liste_titre">
            <td class="titlefield">{php}echo $langs->trans('abv_cat'); {/php}</td>
            <td class="titlefield">{php}echo $langs->trans('abv_type'); {/php}</td>
            <td class="titlefield">{php}echo $langs->trans('abv_value'); {/php}</td>
        </tr>

        {foreach from=$data item=$i}
            <tr class="oddeven">
                <td><input type="hidden" name="id[]" value="{$i.id}">{$i.name}</td>
                <td><select name="type[]">
                        {if $i.type eq NULL}<option value="">--{php}echo $langs->trans('select_option'); {/php}--</option>
                        {/if}
                        <option value="fixed" {if $i.type eq "fixed"}selected {/if}>{php}echo
                            $langs->trans('fixed'); {/php}</option>
                        <option value="percent" {if $i.type eq "percent"}selected {/if}>{php}echo
                            $langs->trans('percent'); {/php}</option>
                    </select></td>
                <td><input type="text" name="val[]" value="{$i.value}"></td>
            </tr>
        {/foreach}

    </table>
    <input type="submit" value="{php}echo $langs->trans('save'); {/php}" class="button">
    <a class="button" href="{$self}?action=updateall">{php}echo $langs->trans('update_prices'); {/php}</a>
</form>
{if $msg[0] eq "OK"}
    <span>{$msg[1]}</span>
{/if}

{if $msg[0] eq "ERR"}
    <span>{$msg[1]}</span>
{/if}
