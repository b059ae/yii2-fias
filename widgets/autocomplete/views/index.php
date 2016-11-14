<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use solbianca\fias\widgets\autocomplete\AutocompleteAsset;

AutocompleteAsset::register($this);

/**
 * @var $this yii\web\View
 * @var $urlAddressObject string
 * @var $urlHouse string
 * @var $regions \solbianca\fias\models\FiasRegion[]
 */

$js = <<<EOD

var autocomplete = {

    'config' : {
        'url': '{$urlAddressObject}',
        'container': '#form-address',
        'input': '.fias-input',
        'minLength': 3,   
    },
    'events': function() {
        $(autocomplete.config.input).keyup(function(event) {
            var th = this;
            autocomplete.getAddresses(autocomplete.config.url, autocomplete.getFormData(), $(this).data('type'), th);
        });
    },

    'init' : function(config) {
        if (config && typeof(config) == 'object') {
            $.extend(myFeature.config, config);
        }
        autocomplete.events();
    },

    'getFormData': function() {
        return $(autocomplete.config.container).serializeArray();
    },

    'getAddresses': function(url, formData, type, th) { 

        formData[formData.length] = {name: "type", value: type};
        var request = $.ajax({
            url: url,
            method: "POST",
            data: formData,
            dataType: "json"
        });

        request.done(function( respond ) {
            if (respond.result !== true) {
                return null;
            } else if (respond.data === null) {
                return null;
            }

            autocomplete.initAutocomplete(respond.data, th);
        });

        request.fail(function( jqXHR, textStatus ) {
            console.log( "Что-то пошло не так." );
        });
    },

    'initAutocomplete': function(data, th) {
        $(th).autocomplete({
            minLength: 3,
            source: data,
            select: function( event, ui ) {
                $(th).val(ui.item.value);
                $('#'+$(th).attr('id')+'-id').val(ui.item.id);
                return false;
            }
        });
    },
};
autocomplete.init();
EOD;
$this->registerJs($js);
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-address',
]) ?>

    <div class="form-group">
        <label for="form-region">Регион</label>
        <?= Html::dropDownList('region', 77, $regions, ['class' => 'form-control', 'id' => 'form-region']) ?>
    </div>
    <div class="form-group">
        <label for="form-street">Город</label>
        <?= Html::textInput('city', null, ['class' => 'form-control fias-input', 'id' => 'form-city', 'data-type' => 'city']) ?>
        <?= Html::hiddenInput('city_id', null, ['id' => 'form-city-id']) ?>
    </div>
    <div class="form-group">
        <label for="form-street">Улица</label>
        <?= Html::textInput('street', null, ['class' => 'form-control fias-input', 'id' => 'form-street', 'data-type' => 'street']) ?>
        <?= Html::hiddenInput('address_id', null, ['id' => 'form-street-id']) ?>
    </div>

<?php ActiveForm::end() ?>