const acceptedStatuses = new Set(['ACCEPTED', 'QUEUED', 'WAITING_FOR_RESULT']);

export function getCalcfgActionResult(response) {
  const result = response?.body?.actionResult;
  return result && acceptedStatuses.has(result.status) ? result : undefined;
}

export function canSubmitCalcfgAction(resource, action) {
  return !!resource?.connected || resource?.calcfgQueueableActions?.[action] === true;
}

export function getCalcfgSubmissionMessage(result, {password = false} = {}) {
  if (result?.status === 'QUEUED') {
    return 'QUEUED';
  }
  if (result?.status === 'WAITING_FOR_RESULT') {
    return password ? 'WAITING_FOR_RESULT_PASSWORD' : 'WAITING_FOR_RESULT';
  }
  return undefined;
}
