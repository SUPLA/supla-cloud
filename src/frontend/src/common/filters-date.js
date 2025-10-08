import {DateTime} from 'luxon';

export function toLuxon(anything) {
  if (typeof anything === 'string') {
    return DateTime.fromISO(anything);
  } else if (typeof anything === 'number') {
    return DateTime.fromSeconds(anything);
  } else if (anything instanceof DateTime) {
    return anything;
  } else {
    throw new Error('Unsupported date format: ' + anything);
  }
}

export function formatDate(datetime, format) {
  if (!datetime) {
    return '-';
  }
  if (typeof format === 'string') {
    format = DateTime[format];
  }
  return toLuxon(datetime).toLocaleString(format);
}

export function formatDateTime(datetime) {
  return formatDate(datetime, DateTime.DATETIME_SHORT);
}

export function formatDateTimeLong(datetime) {
  return formatDate(datetime, DateTime.DATETIME_MED_WITH_WEEKDAY);
}

export function formatDateForHtmlInput(datetime) {
  return DateTime.fromISO(datetime).startOf('minute').toISO({includeOffset: false, suppressSeconds: true, suppressMilliseconds: true});
}
