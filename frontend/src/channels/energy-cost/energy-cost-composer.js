const metadata = (entry) => entry.metadata || entry;
const supplyKinds = ['ENERGY_PURCHASE', 'SUPPLIER_FIXED'];
const distributionKinds = ['DISTRIBUTION_VARIABLE', 'DISTRIBUTION_FIXED'];
export const roleKinds = (role) => (role === 'supply' ? supplyKinds : distributionKinds);

// Starter recipes are the catalog's association between variable and fixed charges.
// Presets not represented by a recipe still retain all their declared components.
export function composerOptions(role, presets, starters) {
  const kinds = roleKinds(role);
  const primary = kinds[0];
  return presets
    .filter((preset) => metadata(preset).components?.some((component) => component.kind === primary))
    .map((preset) => {
      const recipe = starters.find((starter) => starter.components?.some((component) => component.presetId === preset.id && component.kind === primary));
      const components = recipe
        ? recipe.components.filter((component) => kinds.includes(component.kind)).map((component) => ({...component, values: {}}))
        : metadata(preset)
            .components.filter((component) => kinds.includes(component.kind))
            .map(({kind, componentId}) => ({kind, componentId, presetId: preset.id, values: {}}));
      return {preset, components};
    });
}

export function compatibleComponents(components, configuration, details) {
  const ids = new Set();
  let context = configuration.taxContext;
  for (const component of components) {
    if (ids.has(component.componentId)) return false;
    ids.add(component.componentId);
    if (component.presetId === undefined) continue;
    const preset = details[component.presetId];
    const document = preset?.document;
    const definition = document?.billingDefinitionTemplate?.components?.find((candidate) => candidate.id === component.componentId);
    if (!definition || definition.kind !== component.kind || document.currency !== configuration.currency || document.timezone !== configuration.timezone)
      return false;
    if (context && (context.jurisdiction !== document.taxContext?.jurisdiction || context.customerClass !== document.taxContext?.customerClass)) return false;
    context = document.taxContext;
  }
  return true;
}

export function composeRole(period, role, components) {
  return {...period, components: [...period.components.filter((component) => !roleKinds(role).includes(component.kind)), ...components]};
}
