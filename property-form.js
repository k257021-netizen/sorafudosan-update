(() => {
  const typeSelect = document.querySelector('[data-transaction-type]');
  if (!typeSelect) return;

  const updateTypeFields = () => {
    const type = typeSelect.value;
    document.querySelectorAll('[data-type-fields]').forEach((group) => {
      const visible = type === '' || group.dataset.typeFields === type;
      group.hidden = !visible;
      group.querySelectorAll('input, select, textarea').forEach((input) => {
        input.disabled = !visible;
        if (input.hasAttribute('data-type-input')) input.required = visible;
      });
    });
  };

  typeSelect.addEventListener('change', updateTypeFields);
  updateTypeFields();
})();
