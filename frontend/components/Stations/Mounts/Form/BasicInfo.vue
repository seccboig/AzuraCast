<template>
    <tab
        :label="$gettext('Basic Info')"
        :item-header-class="tabClass"
    >
        <div class="row g-3 mb-3">
            <form-group-field
                id="edit_form_name"
                class="col-md-6"
                :field="v$.name"
                :label="$gettext('Mount Point URL')"
                :description="$gettext('This name should always begin with a slash (/), and must be a valid URL, such as /autodj.mp3')"
            />

            <form-group-field
                id="edit_form_display_name"
                class="col-md-6"
                :field="v$.display_name"
                :label="$gettext('Display Name')"
                :description="$gettext('The display name assigned to this mount point when viewing it on administrative or public pages. Leave blank to automatically generate one.')"
            />

            <form-group-checkbox
                id="edit_form_is_visible_on_public_pages"
                class="col-md-6"
                :field="v$.is_visible_on_public_pages"
                :label="$gettext('Show on Public Pages')"
                :description="$gettext('Enable to allow listeners to select and play from this mount point on this station\'s public pages, including embedded widgets.')"
            />

            <form-group-checkbox
                id="edit_form_is_default"
                class="col-md-6"
                :field="v$.is_default"
                :label="$gettext('Set as Default Mount Point')"
                :description="$gettext('If this mount is the default, it will be played on the radio preview and the public radio page in this system.')"
            />

            <form-group-field
                id="edit_form_relay_url"
                class="col-md-6"
                :field="v$.relay_url"
                :label="$gettext('Relay Stream URL')"
                :description="$gettext('Enter the full URL of another stream to relay its broadcast through this mount point.')"
            />

            <form-group-checkbox
                id="edit_form_is_public"
                class="col-md-6"
                :field="v$.is_public"
            >
                <template #label>
                    {{ $gettext('Publish to "Yellow Pages" Directories') }}
                </template>
                <template #description>
                    {{
                        $gettext('Enable to advertise this mount point on "Yellow Pages" public radio directories.')
                    }}
                </template>
            </form-group-checkbox>

            <form-group-field
                id="edit_form_max_listener_duration"
                class="col-md-6"
                :field="v$.max_listener_duration"
                input-type="number"
                :input-attrs="{min: '0', max: '2147483647'}"
                :label="$gettext('Max Listener Duration')"
                :description="$gettext('Set the length of time (seconds) a listener will stay connected to the stream. If set to 0, listeners can stay connected infinitely.')"
            />

            <template v-if="isShoutcast">
                <form-group-field
                    id="edit_form_authhash"
                    class="col-md-6"
                    :field="v$.authhash"
                    :label="$gettext('YP Directory Authorization Hash')"
                >
                    <template #description>
                        {{
                            $gettext('If your stream is set to advertise to YP directories above, you must specify an authorization hash. You can manage these on the Shoutcast web site.')
                        }}
                        <br>
                        <a
                            href="https://radiomanager.shoutcast.com/"
                            target="_blank"
                        >
                            {{ $gettext('Shoutcast Radio Manager') }}
                        </a>
                    </template>
                </form-group-field>
            </template>
            <template v-if="isIcecast">
                <form-group-field
                    id="edit_form_fallback_mount"
                    class="col-md-6"
                    :field="v$.fallback_mount"
                    :label="$gettext('Fallback Mount')"
                    :description="$gettext('If this mount point is not playing audio, listeners will automatically be redirected to this mount point. The default is /error.mp3, a repeating error message.')"
                />
            </template>
        </div>
    </tab>
</template>

<script setup lang="ts">
import FormGroupField from "~/components/Form/FormGroupField.vue";
import FormGroupCheckbox from "~/components/Form/FormGroupCheckbox.vue";
import {computed} from "vue";
import {useVuelidateOnFormTab} from "~/functions/useVuelidateOnFormTab";
import {required} from "@vuelidate/validators";
import Tab from "~/components/Common/Tab.vue";
import {ApiGenericForm, FrontendAdapters} from "~/entities/ApiInterfaces.ts";

const props = defineProps<{
    stationFrontendType: FrontendAdapters
}>();

const form = defineModel<ApiGenericForm>('form', {required: true});

const isIcecast = computed(() => {
    return FrontendAdapters.Icecast === props.stationFrontendType;
});

const isShoutcast = computed(() => {
    return FrontendAdapters.Shoutcast === props.stationFrontendType;
});

const {v$, tabClass} = useVuelidateOnFormTab(
    form,
    computed(() => {
        const validations: {
            [key: string | number]: any
        } = {
            name: {required},
            display_name: {},
            is_visible_on_public_pages: {},
            is_default: {},
            relay_url: {},
            is_public: {},
            max_listener_duration: {required},
        };

        if (isShoutcast.value) {
            validations.authhash = {};
        }

        if (isIcecast.value) {
            validations.fallback_mount = {};
        }

        return validations;
    }),
    () => {
        const blankForm: {
            [key: string | number]: any
        } = {
            name: null,
            display_name: null,
            is_visible_on_public_pages: true,
            is_default: false,
            relay_url: null,
            is_public: true,
            max_listener_duration: 0,
        };

        if (isShoutcast.value) {
            blankForm.authhash = null;
        }

        if (isIcecast.value) {
            blankForm.fallback_mount = '/error.mp3';
        }

        return blankForm;
    }
);
</script>
