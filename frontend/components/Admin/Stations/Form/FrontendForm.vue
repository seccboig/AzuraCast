<template>
    <tab
        :label="$gettext('Broadcasting')"
        :item-header-class="tabClass"
    >
        <div class="row g-3 mb-3">
            <form-group-multi-check
                id="edit_form_frontend_type"
                class="col-md-12"
                :field="v$.frontend_type"
                :options="frontendTypeOptions"
                stacked
                radio
                :label="$gettext('Local Broadcasting Service')"
                :description="$gettext('This software is traditionally used to deliver your broadcast to your listeners. You can still broadcast remotely or via HLS if this service is disabled.')"
            />
        </div>

        <template v-if="isLocalFrontend">
            <div
                v-if="isShoutcastFrontend"
                class="row g-3 mb-3"
            >
                <form-group-field
                    id="edit_form_frontend_sc_license_id"
                    class="col-md-6"
                    :field="v$.frontend_config.sc_license_id"
                    :label="$gettext('Shoutcast License ID')"
                />

                <form-group-field
                    id="edit_form_frontend_sc_user_id"
                    class="col-md-6"
                    :field="v$.frontend_config.sc_user_id"
                    :label="$gettext('Shoutcast User ID')"
                />
            </div>

            <div class="row g-3 mb-3">
                <form-group-field
                    id="edit_form_frontend_source_pw"
                    class="col-md-6"
                    :field="v$.frontend_config.source_pw"
                    :label="$gettext('Customize Source Password')"
                    :description="$gettext('Leave blank to automatically generate a new password.')"
                />

                <form-group-field
                    id="edit_form_frontend_admin_pw"
                    class="col-md-6"
                    :field="v$.frontend_config.admin_pw"
                    :label="$gettext('Customize Administrator Password')"
                    :description="$gettext('Leave blank to automatically generate a new password.')"
                />
            </div>

            <form-fieldset>
                <template #label>
                    {{ $gettext('Advanced Configuration') }}
                    <span class="badge small text-bg-primary ms-2">
                        {{ $gettext('Advanced') }}
                    </span>
                </template>

                <div class="row g-3 mb-3">
                    <form-group-field
                        id="edit_form_frontend_port"
                        class="col-md-6"
                        :field="v$.frontend_config.port"
                        input-type="number"
                        :input-attrs="{min: '0'}"
                        :label="$gettext('Customize Broadcasting Port')"
                        :description="$gettext('No other program can be using this port. Leave blank to automatically assign a port.')"
                    />

                    <form-group-field
                        id="edit_form_max_listeners"
                        class="col-md-6"
                        :field="v$.frontend_config.max_listeners"
                        :label="$gettext('Maximum Listeners')"
                        :description="$gettext('Maximum number of total listeners across all streams. Leave blank to use the default.')"
                    />
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <form-group-field
                            id="edit_form_frontend_banned_ips"
                            :field="v$.frontend_config.banned_ips"
                            input-type="textarea"
                            :input-attrs="{class: 'text-preformatted'}"
                            :label="$gettext('Banned IP Addresses')"
                            :description="$gettext('List one IP address or group (in CIDR format) per line.')"
                        />

                        <form-group-field
                            id="edit_form_frontend_allowed_ips"
                            :field="v$.frontend_config.allowed_ips"
                            input-type="textarea"
                            :input-attrs="{class: 'text-preformatted'}"
                            :label="$gettext('Allowed IP Addresses')"
                            :description="$gettext('List one IP address or group (in CIDR format) per line.')"
                        />

                        <form-group-field
                            id="edit_form_frontend_banned_user_agents"
                            :field="v$.frontend_config.banned_user_agents"
                            input-type="textarea"
                            :input-attrs="{class: 'text-preformatted'}"
                            :label="$gettext('Banned User Agents')"
                            :description="$gettext('List one user agent per line. Wildcards (*) are allowed.')"
                        />
                    </div>

                    <div class="col-md-7">
                        <form-group-select
                            id="edit_form_frontend_banned_countries"
                            :field="v$.frontend_config.banned_countries"
                            :options="countries"
                            multiple
                            :label="$gettext('Banned Countries')"
                            :description="$gettext('Select the countries that are not allowed to connect to the streams.')"
                        />

                        <div class="block-buttons">
                            <button
                                type="button"
                                class="btn btn-block btn-primary"
                                @click="clearCountries"
                            >
                                {{ $gettext('Clear List') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form-fieldset>

            <form-fieldset>
                <template #label>
                    {{ $gettext('Custom Configuration') }}
                    <span class="badge small text-bg-primary ms-2">
                        {{ $gettext('Advanced') }}
                    </span>
                </template>
                <template #description>
                    {{ $gettext('This code will be included in the frontend configuration. Allowed formats are:') }}
                    <ul>
                        <li>JSON: <code>{"new_key": "new_value"}</code></li>
                        <li>XML: <code>&lt;new_key&gt;new_value&lt;/new_key&gt;</code></li>
                    </ul>
                </template>

                <div class="row g-3">
                    <form-group-field
                        id="edit_form_frontend_custom_config"
                        class="col-md-12"
                        :field="v$.frontend_config.custom_config"
                        input-type="textarea"
                        :input-attrs="{class: 'text-preformatted', spellcheck: 'false', 'max-rows': 25, rows: 5}"
                        :label="$gettext('Custom Configuration')"
                    />
                </div>
            </form-fieldset>
        </template>
    </tab>
</template>

<script setup lang="ts">
import FormFieldset from "~/components/Form/FormFieldset.vue";
import FormGroupField from "~/components/Form/FormGroupField.vue";
import {computed} from "vue";
import {useTranslate} from "~/vendor/gettext";
import FormGroupMultiCheck from "~/components/Form/FormGroupMultiCheck.vue";
import FormGroupSelect from "~/components/Form/FormGroupSelect.vue";
import {useVuelidateOnFormTab} from "~/functions/useVuelidateOnFormTab";
import {numeric, required} from "@vuelidate/validators";
import Tab from "~/components/Common/Tab.vue";
import {SimpleFormOptionInput} from "~/functions/objectToFormOptions.ts";
import {ApiGenericForm, FrontendAdapters} from "~/entities/ApiInterfaces.ts";

const props = defineProps<{
    isRsasInstalled: boolean,
    isShoutcastInstalled: boolean,
    countries: Record<string, string>,
}>();

const form = defineModel<ApiGenericForm>('form', {required: true});

const {v$, tabClass} = useVuelidateOnFormTab(
    form,
    {
        frontend_type: {required},
        frontend_config: {
            sc_license_id: {},
            sc_user_id: {},
            source_pw: {},
            admin_pw: {},
            port: {numeric},
            max_listeners: {},
            custom_config: {},
            banned_ips: {},
            banned_countries: {},
            allowed_ips: {},
            banned_user_agents: {}
        },
    },
    () => ({
        frontend_type: FrontendAdapters.Icecast,
        frontend_config: {
            sc_license_id: '',
            sc_user_id: '',
            source_pw: '',
            admin_pw: '',
            port: '',
            max_listeners: '',
            custom_config: '',
            banned_ips: '',
            banned_countries: [],
            allowed_ips: '',
            banned_user_agents: '',
        },
    })
);

const {$gettext} = useTranslate();

const frontendTypeOptions = computed<SimpleFormOptionInput>(() => {
    const frontendOptions: SimpleFormOptionInput = [
        {
            text: $gettext('Use Icecast 2.4 on this server.'),
            value: FrontendAdapters.Icecast
        },
    ];

    if (props.isRsasInstalled) {
        frontendOptions.push({
            text: $gettext('Use Rocket Streaming Audio Server (RSAS) on this server.'),
            value: FrontendAdapters.Rsas
        });
    }

    if (props.isShoutcastInstalled) {
        frontendOptions.push({
            text: $gettext('Use Shoutcast DNAS 2 on this server.'),
            value: FrontendAdapters.Shoutcast
        });
    }

    frontendOptions.push({
        text: $gettext('Do not use a local broadcasting service.'),
        value: FrontendAdapters.Remote
    });

    return frontendOptions;
});

const isLocalFrontend = computed(() => {
    return form.value.frontend_type !== FrontendAdapters.Remote;
});

const isShoutcastFrontend = computed(() => {
    return form.value.frontend_type === FrontendAdapters.Shoutcast;
});

const clearCountries = () => {
    form.value.frontend_config.banned_countries = [];
}
</script>
