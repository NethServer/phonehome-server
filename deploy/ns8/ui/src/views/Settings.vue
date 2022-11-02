<!--
  Copyright (C) 2022 Nethesis S.r.l.
  SPDX-License-Identifier: GPL-3.0-or-later
-->
<template>
  <cv-grid fullWidth>
    <cv-row>
      <cv-column class="page-title">
        <h2>{{ $t("settings.title") }}</h2>
      </cv-column>
    </cv-row>
    <cv-row v-if="error.getConfiguration">
      <cv-column>
        <NsInlineNotification kind="error" :title="$t('action.get-configuration')" :description="error.getConfiguration"
          :showCloseButton="false" />
      </cv-column>
    </cv-row>
    <cv-row>
      <cv-column>
        <cv-tile light>
          <cv-form @submit.prevent="configureModule">
            <cv-text-input :label="$t('settings.hostname')" v-model="hostname" placeholder="phonehome.nethserver.org"
              :disabled="loading.getConfiguration || loading.configureModule" :invalid-message="error.hostname"
              ref="hostname"></cv-text-input>
            <cv-text-input :label="$t('settings.geoip_token')" v-model="geoIpToken"
              placeholder="XXXXXXXXXXXX" :disabled="loading.getConfiguration || loading.configureModule"
              :invalid-message="error.geoIpToken" ref="geo_ip_token"></cv-text-input>
              {{ error.geoIpToken }}
            <cv-select :label="$t('settings.log_level')" :placeholder="$t('settings.log_level')"
              :disabled="loading.getConfiguration || loading.configureModule" :invalid-message="error.logLevel"
              v-model="logLevel" ref="log_level">
              <cv-select-option disabled selected hidden>Choose an option</cv-select-option>
              <cv-select-option value="emergency">{{ $t('settings.log_level_emergency') }}</cv-select-option>
              <cv-select-option value="alert">{{ $t('settings.log_level_alert') }}</cv-select-option>
              <cv-select-option value="critical">{{ $t('settings.log_level_critical') }}</cv-select-option>
              <cv-select-option value="error">{{ $t('settings.log_level_error') }}</cv-select-option>
              <cv-select-option value="warning">{{ $t('settings.log_level_warning') }}</cv-select-option>
              <cv-select-option value="notice">{{ $t('settings.log_level_notice') }}</cv-select-option>
              <cv-select-option value="info">{{ $t('settings.log_level_info') }}</cv-select-option>
              <cv-select-option value="debug">{{ $t('settings.log_level_debug') }}</cv-select-option>
            </cv-select>
            <cv-toggle value="debug" :label="$t('settings.debug')"
              :disabled="loading.getConfiguration || loading.configureModule" :invalid-message="error.debug"
              v-model="debug">
            </cv-toggle>
            <cv-toggle value="http_to_https" :label="$t('settings.http_to_https')"
              :disabled="loading.getConfiguration || loading.configureModule" :invalid-message="error.httpToHttps"
              v-model="httpToHttps">
            </cv-toggle>
            <cv-toggle value="lets_encrypt" :label="$t('settings.lets_encrypt')"
              :disabled="loading.getConfiguration || loading.configureModule" :invalid-message="error.letsEncrypt"
              v-model="letsEncrypt">
            </cv-toggle>
            <cv-row v-if="error.configureModule">
              <cv-column>
                <NsInlineNotification kind="error" :title="$t('action.configure-module')"
                  :description="error.configureModule" :showCloseButton="false" />
              </cv-column>
            </cv-row>
            <NsButton kind="primary" :icon="Save20" :loading="loading.configureModule"
              :disabled="loading.getConfiguration || loading.configureModule">{{ $t("settings.save") }}</NsButton>
          </cv-form>
        </cv-tile>
      </cv-column>
    </cv-row>
  </cv-grid>
</template>

<script>
import to from "await-to-js";
import { mapState } from "vuex";
import {
  QueryParamService,
  UtilService,
  TaskService,
  IconService,
  PageTitleService,
} from "@nethserver/ns8-ui-lib";

export default {
  name: "Settings",
  mixins: [
    TaskService,
    IconService,
    UtilService,
    QueryParamService,
    PageTitleService,
  ],
  pageTitle() {
    return this.$t("settings.title") + " - " + this.appName;
  },
  data() {
    return {
      q: {
        page: "settings",
      },
      urlCheckInterval: null,
      hostname: "",
      geoIpToken: "",
      logLevel: "",
      debug: false,
      httpToHttps: false,
      letsEncrypt: false,
      loading: {
        getConfiguration: false,
        configureModule: false
      },
      error: {
        getConfiguration: "",
        configureModule: "",
        hostname: ""
      }
    };
  },
  computed: {
    ...mapState(["instanceName", "core", "appName"]),
  },
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      vm.watchQueryData(vm);
      vm.urlCheckInterval = vm.initUrlBindingForApp(vm, vm.q.page);
    });
  },
  beforeRouteLeave(to, from, next) {
    clearInterval(this.urlCheckInterval);
    next();
  },
  created() {
    this.getConfiguration();
  },
  methods: {
    async getConfiguration() {
      this.loading.getConfiguration = true;
      this.error.getConfiguration = "";
      const taskAction = "get-configuration";
      const eventId = this.getUuid();

      // register to task error
      this.core.$root.$once(
        `${taskAction}-aborted-${eventId}`,
        this.getConfigurationAborted
      );

      // register to task completion
      this.core.$root.$once(
        `${taskAction}-completed-${eventId}`,
        this.getConfigurationCompleted
      );

      const res = await to(
        this.createModuleTaskForApp(this.instanceName, {
          action: taskAction,
          extra: {
            title: this.$t("action." + taskAction),
            isNotificationHidden: true,
            eventId,
          },
        })
      );
      const err = res[0];

      if (err) {
        console.error(`error creating task ${taskAction}`, err);
        this.error.getConfiguration = this.getErrorMessage(err);
        this.loading.getConfiguration = false;
        return;
      }
    },
    getConfigurationAborted(taskResult, taskContext) {
      console.error(`${taskContext.action} aborted`, taskResult);
      this.error.getConfiguration = this.$t("error.generic_error");
      this.loading.getConfiguration = false;
    },
    getConfigurationCompleted(taskContext, taskResult) {
      this.loading.getConfiguration = false;
      const config = taskResult.output;

      this.hostname = config.hostname
      this.geoIpToken = config.geoip_token
      this.logLevel = config.log_level
      this.debug = config.debug
      this.httpToHttps = config.http_to_https
      this.letsEncrypt = config.lets_encrypt

      this.focusElement("hostname");
    },
    validateConfigureModule() {
      this.clearErrors(this);
      let isValidationOk = true;

      if (!this.hostname) {
        this.error.hostname = this.$t("common.required");

        if (isValidationOk) {
          this.focusElement("hostname");
          isValidationOk = false;
        }
      }
      if (!this.geoIpToken) {
        this.error.geoIpToken = this.$t("common.required");

        if (isValidationOk) {
          this.focusElement("geo_ip_token");
          isValidationOk = false;
        }
      }
      return isValidationOk;
    },
    configureModuleValidationFailed(validationErrors) {
      this.loading.configureModule = false;

      for (const validationError of validationErrors) {
        const param = validationError.parameter;

        // set i18n error message
        this.error[param] = this.$t("settings." + validationError.error);
      }
    },
    async configureModule() {
      const isValidationOk = this.validateConfigureModule();
      if (!isValidationOk) {
        return;
      }

      this.loading.configureModule = true;
      const taskAction = "configure-module";
      const eventId = this.getUuid();

      // register to task error
      this.core.$root.$once(
        `${taskAction}-aborted-${eventId}`,
        this.configureModuleAborted
      );

      // register to task validation
      this.core.$root.$once(
        `${taskAction}-validation-failed-${eventId}`,
        this.configureModuleValidationFailed
      );

      // register to task completion
      this.core.$root.$once(
        `${taskAction}-completed-${eventId}`,
        this.configureModuleCompleted
      );

      const res = await to(
        this.createModuleTaskForApp(this.instanceName, {
          action: taskAction,
          data: {
            hostname: this.hostname,
            geoip_token: this.geoIpToken,
            log_level: this.log_level,
            debug: this.debug,
            http_to_https: this.httpToHttps,
            lets_encrypt: this.letsEncrypt
          },
          extra: {
            title: this.$t("settings.configure_instance", {
              instance: this.instanceName,
            }),
            description: this.$t("common.processing"),
            eventId,
          },
        })
      );
      const err = res[0];

      if (err) {
        console.error(`error creating task ${taskAction}`, err);
        this.error.configureModule = this.getErrorMessage(err);
        this.loading.configureModule = false;
        return;
      }
    },
    configureModuleAborted(taskResult, taskContext) {
      console.error(`${taskContext.action} aborted`, taskResult);
      this.error.configureModule = this.$t("error.generic_error");
      this.loading.configureModule = false;
    },
    configureModuleCompleted() {
      this.loading.configureModule = false;

      // reload configuration
      this.getConfiguration();
    },
  },
};
</script>

<style scoped lang="scss">
@import "../styles/carbon-utils";
</style>
