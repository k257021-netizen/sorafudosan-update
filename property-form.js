(() => {
  const typeSelect = document.querySelector('[data-transaction-type]');
  if (!typeSelect) return;

  const updateTypeFields = () => {
    const type = typeSelect.value;
    document.querySelectorAll('[data-type-fields]').forEach((group) => {
      const visible = type !== '' && group.dataset.typeFields === type;
      group.hidden = !visible;
      group.querySelectorAll('input, select, textarea').forEach((input) => {
        input.disabled = !visible;
        if (input.hasAttribute('data-type-input')) input.required = visible;
      });
    });
  };

  const breakdownToggle = document.querySelector('[data-cost-breakdown-toggle]');
  const costParts = Array.from(document.querySelectorAll('[data-cost-part]'));
  const manualInitialCost = document.querySelector('[data-initial-cost-manual]');
  const costTotalPreview = document.querySelector('[data-cost-total-preview]');

  const updateCostBreakdown = () => {
    if (!breakdownToggle) return;
    const enabled = typeSelect.value === 'rent' && breakdownToggle.checked;
    costParts.forEach((input) => { input.required = enabled; });
    if (manualInitialCost) manualInitialCost.disabled = enabled;

    if (!costTotalPreview) return;
    const values = costParts.map((input) => input.value.trim());
    if (!enabled || values.some((value) => value === '' || !Number.isSafeInteger(Number(value)) || Number(value) < 0)) {
      costTotalPreview.textContent = enabled ? '内訳合計：全項目を入力してください' : '内訳合計：未計算';
      return;
    }
    const total = values.reduce((sum, value) => sum + Number(value), 0);
    costTotalPreview.textContent = Number.isSafeInteger(total)
      ? `内訳合計：${total.toLocaleString('ja-JP')}円`
      : '内訳合計：金額を確認してください';
  };

  typeSelect.addEventListener('change', updateTypeFields);
  if (breakdownToggle) {
    breakdownToggle.addEventListener('change', updateCostBreakdown);
    costParts.forEach((input) => input.addEventListener('input', updateCostBreakdown));
  }
  updateTypeFields();
  updateCostBreakdown();
})();
