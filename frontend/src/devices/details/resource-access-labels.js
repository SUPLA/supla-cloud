export function permissionLabels(mask) {
  const labels = [];
  if (mask & 1) labels.push('READ'); // i18n
  if (mask & 2) labels.push('CONTROL (includes READ)'); // i18n
  if (mask & 4) labels.push('ACTION (all supported physical events)'); // i18n
  return labels;
}
export function statusLabel(status) {
  return {PENDING: 'Pending decision', ACCEPTED: 'Accepted', REJECTED: 'Rejected'}[status]; // i18n
}
export function confirmationText(status, action, deliveryMode = 1) {
  if (action === 'approve') return 'Approve all displayed permissions? This does not configure the requesting device.'; // i18n
  if (action === 'reject') return 'Reject this request? Further requests for this pair stay blocked until you reconsider or remove this rejection.'; // i18n
  if (status === 'ACCEPTED' && deliveryMode === 2)
    return 'Revoke only this user approval? Independent permissions remain. Core applies the change to server delivery.'; // i18n
  if (status === 'ACCEPTED') return 'Revoke only this user approval? Independent permissions remain. An offline source applies revocation after reconnecting.'; // i18n
  if (status === 'REJECTED') return 'Remove this rejection? A later device request may become pending again and will require your approval.'; // i18n
  return 'Cancel this pending request? A later device request may become pending again and will require your approval.'; // i18n
}
