(() => {
  const input = document.querySelector('[data-image-upload]');
  const preview = document.querySelector('[data-image-preview]');
  const status = document.querySelector('[data-image-upload-status]');
  if (!input || !preview || !status) return;

  const maxFiles = Number(input.dataset.maxFiles);
  let objectUrls = [];

  input.addEventListener('change', () => {
    objectUrls.forEach((url) => URL.revokeObjectURL(url));
    objectUrls = [];
    preview.replaceChildren();

    const files = [...input.files];
    const overLimit = files.length > maxFiles;
    status.textContent = overLimit
      ? `写真は最大${maxFiles}枚までです。選択し直してください。`
      : files.length
        ? `${files.length}枚選択中。1枚目がメイン写真です。`
        : `写真は任意です。最大${maxFiles}枚まで選択できます。`;
    status.setAttribute('role', overLimit ? 'alert' : 'status');
    input.setCustomValidity(overLimit ? `写真は最大${maxFiles}枚まで選択できます。` : '');

    files.slice(0, maxFiles).forEach((file, index) => {
      const url = URL.createObjectURL(file);
      objectUrls.push(url);
      const figure = document.createElement('figure');
      const image = document.createElement('img');
      const caption = document.createElement('figcaption');
      image.src = url;
      image.alt = '';
      caption.textContent = index === 0 ? 'メイン写真' : `写真 ${index + 1}`;
      figure.append(image, caption);
      preview.append(figure);
    });
  });
})();
