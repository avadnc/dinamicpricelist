<div>

    {foreach from=$currencies item=$currency}
        {if {$currency['currency']} eq "MXN"}
<h2 style="color:red">1 USD = ${$currency['rate']} MXN <span style="color:black;font-size:small;"> {$currency['date']}</span></h2>
        {/if}
    {/foreach}
</div>
