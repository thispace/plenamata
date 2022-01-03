import { sprintf } from '@wordpress/i18n'

import { __ } from '../dashboard/plugins/i18n'
import { fetchDeterData, fetchLastDate } from '../utils/api'
import { roundNumber, shortDate } from '../utils/filters'

const { DateTime, Interval } = window.luxon

document.defaultView.document.addEventListener('DOMContentLoaded', async () => {
    const updated = await fetchLastDate()
    const lastSync = DateTime.fromISO(updated.last_sync, { zone: 'utc' })
    const lastDate = DateTime.fromISO(updated.deter_last_date, { zone: 'utc' })

    const now = DateTime.now()
    const startOfYear = lastDate.startOf('year')
    const thisYear = await fetchDeterData({ data1: startOfYear.toISODate(), data2: updated.deter_last_date })

    const daysThisYear = Interval.fromDateTimes(startOfYear, lastDate)

    const lastWeek = await fetchDeterData({ data1: lastDate.minus({ weeks: 1 }).toISODate(), data2: updated.deter_last_date })

	document.querySelectorAll('[data-deter]').forEach((el) => {
        const deterLabel = el.dataset.deter

        if (deterLabel === 'hectaresLastWeek') {
            const hectaresLastWeek = Number(lastWeek[0].areamunkm) * 100
            el.textContent = roundNumber(hectaresLastWeek)
        }
        else if (deterLabel === 'hectaresPerDay') {
            const hectaresPerDay = (Number(thisYear[0].areamunkm) * 100) / daysThisYear.count('days')
            el.textContent = roundNumber(hectaresPerDay)
        }
        else if (deterLabel === 'hectaresThisYear') {
            const hectaresThisYear = Number(thisYear[0].areamunkm) * 100
            el.textContent = roundNumber(hectaresThisYear)
        }
        else if (deterLabel === 'sourcesLastWeek') {
            const sourcesLastWeek = sprintf(__('Source: DETER/INPE • Latest Update: %s with alerts detected until %s.', 'plenamata'), shortDate(lastSync.toJSDate()), shortDate(lastDate.toJSDate()))
            el.textContent = sourcesLastWeek
        }
        else if (deterLabel === 'treesEstimative') {
            const treesThisYear = Number(thisYear[0].num_arvores)
            const treesPerSecond = treesThisYear / daysThisYear.count('seconds')

            const endDate = (lastDate.year === now.year) ? now : lastDate.endOf('year')
            const elapsedTime = Interval.fromDateTimes(lastDate, endDate)

            let treeCount = treesThisYear + (elapsedTime.count('seconds') * treesPerSecond)
            el.textContent = roundNumber(treeCount)

            if (lastDate.year === now.year) {
                setInterval(() => {
                    treeCount += treesPerSecond
                    el.textContent = roundNumber(treeCount)
                }, 1000)
            }
        }
        else if (deterLabel === 'treesPerDay') {
            const treesPerDay = Number(thisYear[0].num_arvores) / daysThisYear.count('days')
            el.textContent = roundNumber(treesPerDay)
        }
    })

    document.querySelectorAll('[data-mask=true]').forEach((el) => {
        const num = Number(el.textContent)
        if (num) {
            el.textContent = roundNumber(+num)
        }
    })
})
