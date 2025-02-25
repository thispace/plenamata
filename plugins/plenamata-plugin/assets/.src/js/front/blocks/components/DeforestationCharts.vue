<template>
    <div class="deforestation-charts">
        <template v-if="lastUpdate">
            <fieldset class="deforestation-charts__toggle">
                <label :class="{ active: view === 'year' }">
                    <input type="radio" name="charts-view" value="year" v-model="view">
                    <span>{{ __('Yearly', 'plenamata') }}</span>
                </label>
                <label :class="{ active: view === 'month' }">
                    <input type="radio" name="charts-view" value="month" v-model="view">
                    <span>{{ __('Monthly', 'plenamata') }}</span>
                </label>
                <label :class="{ active: view === 'week' }">
                    <input type="radio" name="charts-view" value="week" v-model="view">
                    <span>{{ __('Weekly', 'plenamata') }}</span>
                </label>
            </fieldset>
            <div class="deforestation-charts__chart">
                <keep-alive>
                    <YearlyDeforestationChart key="year" :date="date" v-if="view === 'year'"/>
                    <MonthlyDeforestationChart key="month" :date="date" v-if="view === 'month'"/>
                    <WeeklyDeforestationChart key="week" :date="date" v-if="view === 'week'"/>
                </keep-alive>
            </div>
            <p class="deforestation-charts__source">
                {{ sprintf(__('Source: DETER/INPE • Latest Update: %s with alerts detected until %s.', 'plenamata'), updated.sync, updated.deter) }}
                {{ sprintf(__('The figures represent deforestation for each year up to %s.', 'plenamata'), previousMonth) }}
                {{ sprintf(__('Weekly and monthly data are from %s.', 'plenamata'), year) }}
            </p>
        </template>
    </div>
</template>

<script>
    import MonthlyDeforestationChart from './MonthlyDeforestationChart.vue'
    import WeeklyDeforestationChart from './WeeklyDeforestationChart.vue'
    import YearlyDeforestationChart from './YearlyDeforestationChart.vue'
    import { _x } from '../../dashboard/plugins/i18n'
    import { fetchLastDate } from '../../utils/api'
    import { shortDate } from '../../utils/filters'

    const { DateTime } = window.luxon
    const months = [
        null,
        _x('January', 'months', 'plenamata'),
        _x('February', 'months', 'plenamata'),
        _x('March', 'months', 'plenamata'),
        _x('April', 'months', 'plenamata'),
        _x('May', 'months', 'plenamata'),
        _x('June', 'months', 'plenamata'),
        _x('July', 'months', 'plenamata'),
        _x('August', 'months', 'plenamata'),
        _x('September', 'months', 'plenamata'),
        _x('October', 'months', 'plenamata'),
        _x('November', 'months', 'plenamata'),
        _x('December', 'months', 'plenamata'),
    ]

    export default {
        name: 'DeforestationCharts',
        components: {
            MonthlyDeforestationChart,
            WeeklyDeforestationChart,
            YearlyDeforestationChart,
        },
        data () {
            return {
                lastUpdate: {},
                view: 'year',
            }
        },
        computed: {
            date () {
                return DateTime.fromISO(this.lastUpdate.deter_last_date)
            },
            previousMonth () {
                const month = this.date.month
                return months[month]
            },
            updated () {
                return {
                    deter: shortDate(this.lastUpdate?.deter_last_date).replaceAll('/', '.'),
                    sync: shortDate(this.lastUpdate?.last_sync).replaceAll('/', '.'),
                }
            },
            year () {
                return this.date.year
            },
        },
        async created () {
            const lastUpdate =  await fetchLastDate()
            this.lastUpdate = lastUpdate
        },
    }
</script>
