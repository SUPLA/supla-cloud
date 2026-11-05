import {describe, expect, it} from 'vitest';
import {canSubmitCalcfgAction, getCalcfgActionResult, getCalcfgSubmissionMessage} from '@/devices/details/calcfg-submission.js';

describe('CALCFG submission feedback', () => {
  it('uses explicit receipts and preserves old responses without a receipt', () => {
    expect(getCalcfgActionResult({body: {actionResult: {status: 'QUEUED', id: 12, queuedAt: 1793577600}}})).toEqual({
      status: 'QUEUED',
      id: 12,
      queuedAt: 1793577600,
    });
    expect(getCalcfgActionResult({body: {}})).toBeUndefined();
    expect(getCalcfgActionResult({body: {actionResult: {status: 'REJECTED'}}})).toBeUndefined();
    expect(getCalcfgActionResult({body: {actionResult: {status: 'ACCEPTED', id: 12, queuedAt: null}}}).status).toBe('ACCEPTED');
  });

  it('allows offline submission only when backend eligibility names the action', () => {
    expect(canSubmitCalcfgAction({connected: false, calcfgQueueableActions: {restartDevice: true}}, 'restartDevice')).toBe(true);
    expect(canSubmitCalcfgAction({connected: false, calcfgQueueableActions: {restartDevice: true}}, 'factoryReset')).toBe(false);
    expect(canSubmitCalcfgAction({connected: true, calcfgQueueableActions: {}}, 'restartDevice')).toBe(true);
  });

  it('distinguishes queued work from an earlier operation awaiting its result', () => {
    expect(getCalcfgSubmissionMessage({status: 'QUEUED'})).toBe('QUEUED');
    expect(getCalcfgSubmissionMessage({status: 'WAITING_FOR_RESULT'}, {password: true})).toBe('WAITING_FOR_RESULT_PASSWORD');
    expect(getCalcfgSubmissionMessage({status: 'WAITING_FOR_RESULT'})).toBe('WAITING_FOR_RESULT');
  });
});
