<?php

$brands           = []; /** Useless variable */
$arOrder          = array( 'SORT' => 'ASC' );
$filter           = array( 'IBLOCK_ID' => 6 );
$arGroupBy        = false;
$arNavStartParams = false;
$select           = array(); /** Shouldn't be empty. Because of documentation */
$res              = CIBlockElement::GetList(
    $arOrder,
    $filter,
    $arGroupBy,
    $arNavStartParams,
    $select
);
while( $product = $res->GetNextElement() ){
    $fields     = $product->GetFields();
    $properties = $product->GetPropepties();
    $resBrand   = CIBlockElement::GetByID( $properties[ "BRAND" ]["VALUE"] );
    if( $brand = $resBrand->GetNext() ){
        $brands[] = $brand["NAME"];
    }
}
$arResult["BRANDS"] = $brands;
?>
    <div class="container-slider brands">
        <? /** Short tag usage. Not recommended by documentation */
            foreach( $arResult["BRANDS"] as $brand ):
        ?>
            <div class="slide-brand general-background"><?= $brand ?>></div>
        <? endforeach; ?>
    </div>
<?