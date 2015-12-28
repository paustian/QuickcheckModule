{% form_theme form 'ZikulaFormExtensionBundle:Form:bootstrap_3_zikula_admin_layout.html.twig' %}
{% include 'PaustianQuickcheckModule:Admin:quickcheck_admin_menu.html.twig' %}
<div class="container-fluid">
    
    <div class="row">
        <h3>
            <span class="fa fa-edit"></span>
            {{ __(Edit or Delete Questions") }}
        </h3>
        <p>{{ __("Select a question to edit or delete. You can also select a series of questions to delete, by clicking on the check box and then choosing the delete button") }}</p>
        {{ showflashes() }}
        {{ form_start(form) }}
        {{ form_errors(form) }}
        <fieldset>
            {{ questions }}
        </fieldset>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {{ form_widget(form.save, {'attr': {'class': 'btn btn-success'}}) }}
                {{ form_widget(form.cancel, {'attr': {'class': 'btn btn-success'}}) }}
            </div>
        </div>
        {{ form_end(form) }}
    </div>
</div>
{{ render(controller('ZikulaAdminModule:Admin:adminfooter')) }}