import Konva from 'konva';

(() => {
  'use strict';

  // Guard — mendukung dua konteks: halaman klien dan halaman QA reject
  const PAGE = document.body?.dataset.page;
  if (PAGE !== 'client-report' && PAGE !== 'qa-reject') return;

  // Prefix ID untuk elemen di konteks QA (agar tidak bentrok dengan halaman klien)
  const IS_QA = PAGE === 'qa-reject';
  const ID = IS_QA
    ? {
        form:                    'qa-reject-form',
        input:                   'qa-reject-attachments',
        dropzone:                'qa-reject-dropzone',
        emptyState:              'qa-reject-dropzone-empty',
        previewWrapper:          'qa-reject-preview-wrapper',
        previewList:             'qa-reject-preview-list',
        annotationWorkspace:     'qa-reject-annotation-workspace',
        annotationCanvasContainer: 'qa-reject-annotation-canvas',
        annotationFileLabel:     'qa-reject-annotation-file-label',
        annotationClose:         'qa-reject-annotation-close',
        annotationSave:          'qa-reject-annotation-save',
        annotationStatus:        'qa-reject-annotation-status',
        annotationToolbar:       'qa-reject-annotation-toolbar',
        addButton:               'qa-reject-attachment-add-btn',
        errorText:               'qa-reject-error-attachments',
        annotationUndo:          'qa-reject-annotation-undo',
        annotationRedo:          'qa-reject-annotation-redo',
        annotationDelete:        'qa-reject-annotation-delete',
        annotationClearAll:      'qa-reject-annotation-clear-all',
      }
    : {
        form:                    'clientReportForm',
        input:                   'attachments',
        dropzone:                'attachmentDropzone',
        emptyState:              'attachmentDropzoneEmptyState',
        previewWrapper:          'attachmentPreviewWrapper',
        previewList:             'attachmentPreviewList',
        annotationWorkspace:     'annotationWorkspace',
        annotationCanvasContainer: 'annotationCanvasContainer',
        annotationFileLabel:     'annotationFileLabel',
        annotationClose:         'annotationClose',
        annotationSave:          'annotationSave',
        annotationStatus:        'annotationStatus',
        annotationToolbar:       'annotationToolbar',
        addButton:               'attachmentAddBtn',
        errorText:               'error-attachments',
        annotationUndo:          'annotationUndo',
        annotationRedo:          'annotationRedo',
        annotationDelete:        'annotationDelete',
        annotationClearAll:      'annotationClearAll',
      };

  // State
  const state = {
    files: [], // [{ id, file, annotatedBlob, annotatedFile, hasAnnotation }]
    activeIndex: null,
    stage: null,
    imageLayer: null,
    drawLayer: null,
    transformerLayer: null,
    transformer: null,
    imageNode: null,
    baseImageWidth: 0,
    baseImageHeight: 0,
    stageScale: 1,
    canvasInitToken: 0,
    tool: 'select',
    color: '#EF4444',
    history: [],
    historyIndex: -1,
    drawingRect: null,
    isDrawingRectangle: false,
    rectangleStart: null,
    drawingArrow: null,
    isDrawingArrow: false,
    arrowStart: null,
    drawingFreehand: null,
    isDrawingFreehand: false,
    activeTextEditor: null,
  };

  // Konstanta
  const MAX_FILES = 5;
  const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  const MAX_HISTORY_ENTRIES = 30;

  const elements = {
    form:                      document.getElementById(ID.form),
    input:                     document.getElementById(ID.input),
    dropzone:                  document.getElementById(ID.dropzone),
    emptyState:                document.getElementById(ID.emptyState),
    previewWrapper:            document.getElementById(ID.previewWrapper),
    previewList:               document.getElementById(ID.previewList),
    annotationWorkspace:       document.getElementById(ID.annotationWorkspace),
    annotationCanvasContainer: document.getElementById(ID.annotationCanvasContainer),
    annotationFileLabel:       document.getElementById(ID.annotationFileLabel),
    annotationClose:           document.getElementById(ID.annotationClose),
    annotationSave:            document.getElementById(ID.annotationSave),
    annotationStatus:          document.getElementById(ID.annotationStatus),
    annotationToolbar:         document.getElementById(ID.annotationToolbar),
    addButton:                 document.getElementById(ID.addButton),
    errorText:                 document.getElementById(ID.errorText),
    // Toolbar buttons di-scope ke toolbar container agar tidak bentrok lintas konteks
    annotationToolButtons: [],
    annotationColorButtons: [],
    annotationUndo:   document.getElementById(ID.annotationUndo),
    annotationRedo:   document.getElementById(ID.annotationRedo),
    annotationDelete: document.getElementById(ID.annotationDelete),
    annotationClearAll: document.getElementById(ID.annotationClearAll),
  };

  // Scoped query: hanya ambil tool/color buttons dari toolbar yang relevan
  if (elements.annotationToolbar) {
    elements.annotationToolButtons = Array.from(
      elements.annotationToolbar.querySelectorAll('[data-tool]')
    );
    elements.annotationColorButtons = Array.from(
      elements.annotationToolbar.querySelectorAll('[data-color]')
    );
  }

  if (!elements.input || !elements.dropzone || !elements.previewList) return;

  const previewUrlById = new Map();
  let statusTimer = null;
  let dropzoneDragDepth = 0;

  function t(key, replacements = {}, fallback = '') {
    if (typeof window.getClientReportText === 'function') {
      return window.getClientReportText(key, replacements, fallback);
    }

    return fallback || key;
  }

  function getFallbackLang() {
    if (typeof window.getClientReportLang === 'function') {
      return window.getClientReportLang() === 'id' ? 'id' : 'en';
    }

    if (typeof window.__clientInitialLang === 'string') {
      return window.__clientInitialLang === 'id' ? 'id' : 'en';
    }

    return document.documentElement.lang === 'id' ? 'id' : 'en';
  }

  function fallbackByLang(messages) {
    const fallbackLang = getFallbackLang();
    return messages[fallbackLang] || messages.en || '';
  }

  function getMaxAttachmentsError() {
    return t(
      'report_validation_max_attachments',
      { count: MAX_FILES },
      fallbackByLang({
        en: `Maximum ${MAX_FILES} images.`,
        id: `Maksimal ${MAX_FILES} gambar.`,
      })
    );
  }

  function getImagesOnlyError() {
    return t(
      'report_validation_image_only',
      {},
      fallbackByLang({
        en: 'Only image files are allowed.',
        id: 'Hanya file gambar yang diperbolehkan.',
      })
    );
  }

  function setDropzoneDraggingState(isDragging) {
    if (!elements.dropzone) return;
    elements.dropzone.classList.toggle('is-dragging', Boolean(isDragging));
  }

  function createId() {
    if (window.crypto?.randomUUID) {
      return window.crypto.randomUUID();
    }

    return `attachment-${Date.now()}-${Math.random().toString(16).slice(2)}`;
  }

  function formatFileSize(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  }

  function getPreviewUrl(fileItem) {
    if (!fileItem) return '';

    // Prioritaskan file hasil anotasi jika ada
    const targetFile = fileItem.annotatedFile || fileItem.file;

    if (!previewUrlById.has(fileItem.id)) {
      previewUrlById.set(fileItem.id, URL.createObjectURL(targetFile));
    }

    return previewUrlById.get(fileItem.id);
  }

  function refreshPreviewUrl(fileId) {
    const item = state.files.find(f => f.id === fileId);
    if (!item) return;

    revokePreviewUrl(fileId);
    getPreviewUrl(item); // Generate URL baru (akan otomatis pakai annotatedFile jika ada)
  }

  function revokePreviewUrl(fileId) {
    const existingUrl = previewUrlById.get(fileId);
    if (!existingUrl) return;

    URL.revokeObjectURL(existingUrl);
    previewUrlById.delete(fileId);
  }

  function clearError() {
    if (!elements.errorText) return;
    elements.errorText.textContent = '';
    delete elements.errorText.dataset.clientErrorType;
    elements.errorText.classList.add('hidden');
  }

  function showError(message) {
    if (!elements.errorText) return;
    elements.errorText.textContent = message;
    delete elements.errorText.dataset.clientErrorType;
    elements.errorText.classList.remove('hidden');
  }

  function setDropzoneErrorState(isError) {
    if (!elements.dropzone) return;
    elements.dropzone.classList.toggle('report-upload-dropzone-error', Boolean(isError));
    elements.dropzone.setAttribute('aria-invalid', isError ? 'true' : 'false');
  }

  function setAttachmentInputErrorState(isError) {
    if (!elements.input) return;

    elements.input.classList.toggle('report-field-input-error', Boolean(isError));
    elements.input.classList.toggle('report-field-input-normal', !isError);
    elements.input.setAttribute('aria-invalid', isError ? 'true' : 'false');
  }

  function hasAttachmentFiles() {
    const inputFileCount = elements.input?.files?.length || 0;
    return inputFileCount > 0 || state.files.length > 0;
  }

  function showAttachmentRequiredError() {
    setDropzoneErrorState(true);
    setAttachmentInputErrorState(true);

    const message = t(
      'report_validation_attachment_required',
      {},
      fallbackByLang({
        en: 'Attach at least 1 image before submitting report.',
        id: 'Lampirkan minimal 1 gambar sebelum mengirim laporan.',
      })
    );
    showError(message);

    if (elements.errorText) {
      elements.errorText.dataset.clientErrorType = 'attachment-required';
    }

    elements.dropzone?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    elements.dropzone?.focus({ preventScroll: true });
  }

  function clearAttachmentRequiredError() {
    setDropzoneErrorState(false);
    setAttachmentInputErrorState(false);

    if (elements.errorText?.dataset.clientErrorType === 'attachment-required') {
      clearError();
    }
  }

  function resetToolSelectionState() {
    state.tool = 'select';
    updateActiveToolButton();
    updateDrawLayerDraggableState();
    updateCanvasCursor();
  }

  function syncToolbarAvailability() {
    const hasCanvas = Boolean(state.stage);
    setToolbarDisabled(!hasCanvas);
  }

  function canHandleCanvasShortcut() {
    return Boolean(state.stage);
  }

  function handleCanvasKeyboardShortcuts(event) {
    if (!canHandleCanvasShortcut()) return;

    const key = String(event.key || '').toLowerCase();
    const isUndo = event.ctrlKey && !event.shiftKey && key === 'z';
    const isRedo =
      event.ctrlKey &&
      ((!event.shiftKey && key === 'y') || (event.shiftKey && key === 'z'));
    const isDeleteKey = key === 'delete' || key === 'backspace';
    const isEditingText = Boolean(state.activeTextEditor || isTypingElement());

    if (isUndo) {
      if (isEditingText) return;
      event.preventDefault();
      undoHistory();
      return;
    }

    if (isRedo) {
      if (isEditingText) return;
      event.preventDefault();
      redoHistory();
      return;
    }

    if (isDeleteKey) {
      if (isEditingText) return;
      event.preventDefault();
      deleteSelectedNodes();
    }
  }

  function getToolbarButtons() {
    if (!elements.annotationToolbar) return [];
    return Array.from(elements.annotationToolbar.querySelectorAll('button'));
  }

  function setToolbarDisabled(isDisabled) {
    getToolbarButtons().forEach((button) => {
      button.disabled = Boolean(isDisabled);
      button.setAttribute('aria-disabled', isDisabled ? 'true' : 'false');
    });
  }

  function getToolCursor() {
    return state.tool === 'select' ? 'default' : 'crosshair';
  }

  function updateCanvasCursor(target = null) {
    const stageContainer = state.stage?.container?.();
    if (!stageContainer) return;

    let cursor = getToolCursor();

    if (state.tool === 'select' && target && isDrawLayerNode(target)) {
      cursor = 'move';
    }

    stageContainer.style.cursor = cursor;
  }

  function isTypingElement(target = document.activeElement) {
    return (
      target?.tagName === 'INPUT' ||
      target?.tagName === 'TEXTAREA' ||
      target?.isContentEditable
    );
  }

  function getAnnotationStatusElement() {
    if (!elements.annotationStatus) {
      const toolbar = document.getElementById('annotationToolbar');
      if (toolbar && toolbar.parentElement) {
        const statusElement = document.createElement('div');
        statusElement.id = 'annotationStatus';
        statusElement.className = 'report-annotation-status';
        toolbar.insertAdjacentElement('afterend', statusElement);
        elements.annotationStatus = statusElement;
      }
    }

    if (!elements.annotationStatus) return null;

    if (!elements.annotationStatus.textContent?.trim()) {
      elements.annotationStatus.style.opacity = '0';
    }

    return elements.annotationStatus;
  }

  function showStatus(message, type = 'success') {
    const statusElement = getAnnotationStatusElement();
    if (!statusElement) return;

    clearTimeout(statusTimer);

    statusElement.textContent = message || '';
    statusElement.classList.remove('is-success', 'is-danger');

    if (!message) {
      statusElement.style.opacity = '0';
      return;
    }

    statusElement.classList.add(type === 'danger' ? 'is-danger' : 'is-success');
    statusElement.style.opacity = '1';

    statusTimer = window.setTimeout(() => {
      statusElement.textContent = '';
      statusElement.classList.remove('is-success', 'is-danger');
      statusElement.style.opacity = '0';
    }, 3000);
  }

    // ── QA-only: hitung chrome height (header+toolbar+status) ──
  function getQAChromeHeight() {
    if (!IS_QA) return 0;

    const workspace = elements.annotationWorkspace;
    if (!workspace) return 100;

    const shell = workspace.querySelector('.report-annotation-shell');
    if (!shell) return 100;

    const canvasArea = shell.querySelector('.report-annotation-canvas');
    let chrome = 0;

    for (const child of shell.children) {
      if (child === canvasArea) continue;
      chrome += child.offsetHeight || 0;
    }

    return chrome || 100;
  }



  function getCanvasContainerWidth() {
    if (!elements.annotationCanvasContainer) return 0;

    const measuredWidth =
      elements.annotationCanvasContainer.clientWidth ||
      elements.annotationCanvasContainer.getBoundingClientRect().width ||
      elements.annotationCanvasContainer.parentElement?.clientWidth ||
      0;

    return Math.max(Math.floor(measuredWidth), 0);
  }

  function isDrawLayerNode(node) {
    if (!node || !state.drawLayer || typeof node.getLayer !== 'function') return false;
    return node.getLayer() === state.drawLayer;
  }

  function isTextNode(node) {
    return isDrawLayerNode(node) && node.getClassName?.() === 'Text';
  }

  function clearSelection() {
    if (!state.transformer) return;
    state.transformer.nodes([]);
    state.transformerLayer?.batchDraw();
  }

  function selectNode(node) {
    if (!state.transformer || !isDrawLayerNode(node)) {
      clearSelection();
      return;
    }

    state.transformer.nodes([node]);
    state.transformerLayer?.batchDraw();
  }

  function updateDrawLayerDraggableState() {
    if (!state.drawLayer) return;

    const shouldBeDraggable = state.tool === 'select';
    Array.from(state.drawLayer.getChildren()).forEach((node) => {
      node.draggable(shouldBeDraggable);
    });

    if (!shouldBeDraggable) {
      clearSelection();
    }

    state.drawLayer.batchDraw();
    updateCanvasCursor();
  }

  function finishActiveTextEditing({ commit = true } = {}) {
    const activeEditor = state.activeTextEditor;
    if (!activeEditor) return;

    const { textarea, textNode, initialText, isNewNode, onBlur, onKeyDown, onInput } = activeEditor;

    state.activeTextEditor = null;

    textarea.removeEventListener('blur', onBlur);
    textarea.removeEventListener('keydown', onKeyDown);
    textarea.removeEventListener('input', onInput);

    const nextText = commit ? textarea.value : initialText;
    const hasText = Boolean(nextText.trim());

    textarea.remove();

    if (!isTextNode(textNode)) return;

    if (!hasText) {
      textNode.destroy();
    } else {
      textNode.text(nextText);
      textNode.show();
      textNode.draggable(state.tool === 'select');
    }

    state.drawLayer?.batchDraw();

    if (!commit) return;

    let changed = false;

    if (isNewNode) {
      changed = hasText;
    } else if (hasText) {
      changed = nextText !== initialText;
    } else {
      changed = Boolean(initialText.trim());
    }

    if (changed) {
      saveHistorySnapshot();
    }
  }

  function cancelActiveTextEditing() {
    finishActiveTextEditing({ commit: false });
  }

  function startInlineTextEditing(textNode, { initialValue = textNode.text(), isNewNode = false } = {}) {
    if (!state.stage || !state.drawLayer || !isTextNode(textNode)) return;

    finishActiveTextEditing({ commit: true });

    const stageBox = state.stage.container().getBoundingClientRect();
    const textPosition = textNode.absolutePosition();
    const scaleX = state.stage.scaleX() || 1;
    const scaleY = state.stage.scaleY() || 1;
    const fontSize = Number(textNode.fontSize()) || 16;

    const textarea = document.createElement('textarea');
    document.body.appendChild(textarea);

    textarea.style.position = 'fixed';
textarea.style.top = `${stageBox.top + textPosition.y * scaleY}px`;
textarea.style.left = `${stageBox.left + textPosition.x * scaleX}px`;
    textarea.style.fontSize = `${fontSize}px`;
    textarea.style.fontFamily = textNode.fontFamily() || 'Inter, sans-serif';
    textarea.style.color = textNode.fill() || state.color;
    textarea.style.border = '1px dashed #6B7280';
    textarea.style.borderRadius = '4px';
    textarea.style.padding = '2px 4px';
    textarea.style.background = 'rgba(255,255,255,0.9)';
    textarea.style.minWidth = '100px';
    textarea.style.width = `${Math.max((textNode.width() || 0) * scaleX, 100)}px`;
    textarea.style.lineHeight = String(textNode.lineHeight?.() || 1.2);
    textarea.style.resize = 'none';
    textarea.style.overflow = 'hidden';
    textarea.style.zIndex = '1000';

    textarea.value = initialValue ?? '';

    const autoResize = () => {
      textarea.style.height = 'auto';
      textarea.style.height = `${Math.max(textarea.scrollHeight, fontSize + 8)}px`;
    };

    const onBlur = () => {
      finishActiveTextEditing({ commit: true });
    };

    const onKeyDown = (event) => {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        finishActiveTextEditing({ commit: true });
        return;
      }

      if (event.key === 'Escape') {
        event.preventDefault();
        finishActiveTextEditing({ commit: false });
      }
    };

    const onInput = () => {
      autoResize();
    };

    state.activeTextEditor = {
      textarea,
      textNode,
      initialText: initialValue ?? '',
      isNewNode,
      onBlur,
      onKeyDown,
      onInput,
    };

    textNode.hide();
    state.drawLayer.batchDraw();

    autoResize();

    textarea.addEventListener('blur', onBlur);
    textarea.addEventListener('keydown', onKeyDown);
    textarea.addEventListener('input', onInput);
    textarea.focus();
    textarea.select();
  }

  function saveHistorySnapshot() {
    if (!state.drawLayer) return;
    const snapshot = state.drawLayer.toJSON();
    state.history = state.history.slice(0, state.historyIndex + 1);
    state.history.push(snapshot);
    if (state.history.length > MAX_HISTORY_ENTRIES) state.history.shift();
    state.historyIndex = state.history.length - 1;
  }

  function restoreDrawLayerFromSnapshot(snapshot) {
    if (!state.drawLayer || !snapshot) return;

    clearSelection();
    state.drawLayer.destroyChildren();

    const parsedLayer = Konva.Node.create(snapshot);
    if (parsedLayer) {
      const children = parsedLayer.getChildren();
      Array.from(children).forEach((child) => {
        child.remove();
        state.drawLayer.add(child);
      });
      parsedLayer.destroy();
    }

    updateDrawLayerDraggableState();
    state.drawLayer.batchDraw();
  }

  function undoHistory() {
    if (!state.drawLayer || state.history.length === 0) return;
    if (state.historyIndex <= 0) return;

    state.historyIndex -= 1;
    restoreDrawLayerFromSnapshot(state.history[state.historyIndex]);
  }

  function redoHistory() {
    if (!state.drawLayer || state.historyIndex >= state.history.length - 1) return;

    state.historyIndex += 1;
    restoreDrawLayerFromSnapshot(state.history[state.historyIndex]);
    state.drawLayer.batchDraw();
  }

  function deleteSelectedNodes() {
    if (!state.drawLayer || !state.transformer) return;

    const selectedNodes = state.transformer.nodes();
    if (!selectedNodes.length) return;

    selectedNodes.forEach((node) => {
      if (!isDrawLayerNode(node)) return;
      node.destroy();
    });

    state.transformer.nodes([]);
    state.transformerLayer?.batchDraw();
    state.drawLayer.batchDraw();
    saveHistorySnapshot();
  }

  function clearAllDrawings() {
    if (!state.drawLayer) return;

    cancelActiveTextEditing();

    state.drawLayer.destroyChildren();
    state.transformer?.nodes([]);
    state.transformerLayer?.batchDraw();
    state.drawLayer.batchDraw();
    saveHistorySnapshot();
  }

  function startRectangleDrawing(stageEvent) {
    if (!state.stage || !state.drawLayer) return;

    const pointerFromStage = state.stage.getPointerPosition();
    let pointer = pointerFromStage
      ? { x: pointerFromStage.x, y: pointerFromStage.y }
      : null;

    if (!pointer && stageEvent?.evt) {
      const stageBox = state.stage.container().getBoundingClientRect();
      const scaleX = state.stage.scaleX() || 1;
      const scaleY = state.stage.scaleY() || 1;

      pointer = {
        x: (stageEvent.evt.clientX - stageBox.left) / scaleX,
        y: (stageEvent.evt.clientY - stageBox.top) / scaleY,
      };
    }

    if (!pointer) return;

    clearSelection();
    state.rectangleStart = { x: pointer.x, y: pointer.y };
    state.isDrawingRectangle = true;

    state.drawingRect = new Konva.Rect({
      x: pointer.x,
      y: pointer.y,
      width: 0,
      height: 0,
      stroke: state.color,
      strokeWidth: 2,
      fill: 'transparent',
      draggable: false,
    });

    state.drawLayer.add(state.drawingRect);
    state.drawLayer.batchDraw();
  }

  function updateRectangleDrawing() {
    if (!state.stage || !state.isDrawingRectangle || !state.drawingRect || !state.rectangleStart) return;

    const pointer = state.stage.getPointerPosition();
    if (!pointer) return;

    const x = Math.min(state.rectangleStart.x, pointer.x);
    const y = Math.min(state.rectangleStart.y, pointer.y);
    const width = Math.abs(pointer.x - state.rectangleStart.x);
    const height = Math.abs(pointer.y - state.rectangleStart.y);

    state.drawingRect.setAttrs({ x, y, width, height });
    state.drawLayer.batchDraw();
  }

  function finishRectangleDrawing() {
    if (!state.isDrawingRectangle || !state.drawLayer) return;

    const rect = state.drawingRect;

    state.isDrawingRectangle = false;
    state.drawingRect = null;
    state.rectangleStart = null;

    if (!rect) return;

    const tooSmall = rect.width() < 5 || rect.height() < 5;

    if (tooSmall) {
      rect.destroy();
      state.drawLayer.batchDraw();
      return;
    }

    rect.draggable(state.tool === 'select');
    state.drawLayer.batchDraw();
    saveHistorySnapshot();
  }

  function startArrowDrawing() {
    if (!state.stage || !state.drawLayer) return;

    const pointer = state.stage.getPointerPosition();
    if (!pointer) return;

    clearSelection();
    state.arrowStart = { x: pointer.x, y: pointer.y };
    state.isDrawingArrow = true;

    state.drawingArrow = new Konva.Arrow({
      points: [pointer.x, pointer.y, pointer.x, pointer.y],
      stroke: state.color,
      strokeWidth: 2,
      fill: state.color,
      pointerLength: 10,
      pointerWidth: 8,
      draggable: false,
    });

    state.drawLayer.add(state.drawingArrow);
    state.drawLayer.batchDraw();
  }

  function updateArrowDrawing() {
    if (!state.stage || !state.isDrawingArrow || !state.drawingArrow || !state.arrowStart) return;

    const pointer = state.stage.getPointerPosition();
    if (!pointer) return;

    state.drawingArrow.points([state.arrowStart.x, state.arrowStart.y, pointer.x, pointer.y]);
    state.drawLayer.batchDraw();
  }

  function finishArrowDrawing() {
    if (!state.isDrawingArrow || !state.drawLayer) return;

    const arrow = state.drawingArrow;

    state.isDrawingArrow = false;
    state.drawingArrow = null;
    state.arrowStart = null;

    if (!arrow) return;

    const points = arrow.points();
    const dx = (points[2] || 0) - (points[0] || 0);
    const dy = (points[3] || 0) - (points[1] || 0);
    const tooShort = Math.hypot(dx, dy) < 5;

    if (tooShort) {
      arrow.destroy();
      state.drawLayer.batchDraw();
      return;
    }

    arrow.draggable(state.tool === 'select');
    state.drawLayer.batchDraw();
    saveHistorySnapshot();
  }

  function startFreehandDrawing() {
    if (!state.stage || !state.drawLayer) return;

    const pointer = state.stage.getPointerPosition();
    if (!pointer) return;

    clearSelection();
    state.isDrawingFreehand = true;

    state.drawingFreehand = new Konva.Line({
      points: [pointer.x, pointer.y],
      stroke: state.color,
      strokeWidth: 2,
      tension: 0.5,
      lineCap: 'round',
      lineJoin: 'round',
      draggable: false,
    });

    state.drawLayer.add(state.drawingFreehand);
    state.drawLayer.batchDraw();
  }

  function updateFreehandDrawing() {
    if (!state.stage || !state.isDrawingFreehand || !state.drawingFreehand) return;

    const pointer = state.stage.getPointerPosition();
    if (!pointer) return;

    const nextPoints = state.drawingFreehand.points().slice();
    nextPoints.push(pointer.x, pointer.y);
    state.drawingFreehand.points(nextPoints);
    state.drawLayer.batchDraw();
  }

  function finishFreehandDrawing() {
    if (!state.isDrawingFreehand || !state.drawLayer) return;

    const line = state.drawingFreehand;

    state.isDrawingFreehand = false;
    state.drawingFreehand = null;

    if (!line) return;

    line.draggable(state.tool === 'select');
    state.drawLayer.batchDraw();
    saveHistorySnapshot();
  }

  function handleTextToolMouseDown(event) {
    if (!state.stage || !state.drawLayer) return;

    const target = event.target;
    if (!target) return;

    const clickedTransformer = Boolean(target.findAncestor?.('Transformer', true));
    if (clickedTransformer) return;

    const clickedExistingText = isDrawLayerNode(target) && target.getClassName?.() === 'Text';
    if (clickedExistingText) {
      startInlineTextEditing(target, { initialValue: target.text(), isNewNode: false });
      return;
    }

    const pointer =
      state.stage.getPointerPosition() ??
      (() => {
        const rect = state.stage.container().getBoundingClientRect();
        const evt = event.evt ?? event;
        return {
          x: (evt.clientX - rect.left) / (state.stage.scaleX() || 1),
          y: (evt.clientY - rect.top) / (state.stage.scaleY() || 1),
        };
      })();

    clearSelection();

    const textNode = new Konva.Text({
      x: pointer.x,
      y: pointer.y,
      text: 'Text',
      fill: state.color,
      fontSize: 16,
      fontFamily: 'Inter, sans-serif',
      draggable: false,
    });

    state.drawLayer.add(textNode);
    state.drawLayer.batchDraw();

    requestAnimationFrame(() => {
      startInlineTextEditing(textNode, {
        initialValue: '',
        isNewNode: true,
      });
    });
  }

  function handleSelectToolMouseDown(event) {
    if (!state.stage || !state.imageNode) return;

    const target = event.target;
    if (!target) return;

    const clickedTransformer = Boolean(target.findAncestor?.('Transformer', true));
    if (clickedTransformer) return;

    if (target === state.stage || target === state.imageNode) {
      clearSelection();
      return;
    }

    if (isDrawLayerNode(target)) {
      selectNode(target);
      target.draggable(true);
      state.drawLayer.batchDraw();
      return;
    }

    clearSelection();
  }

  function bindCanvasInteractions() {
    if (!state.stage || !state.drawLayer) return;

    updateCanvasCursor();

    state.stage.on('mousedown', (event) => {
      if (state.tool === 'rectangle') {
        startRectangleDrawing(event);
        return;
      }

      if (state.tool === 'arrow') {
        startArrowDrawing();
        return;
      }

      if (state.tool === 'freehand') {
        startFreehandDrawing();
        return;
      }

      if (state.tool === 'text') {
        handleTextToolMouseDown(event);
        return;
      }

      if (state.tool === 'select') {
        handleSelectToolMouseDown(event);
      }
    });

    state.stage.on('mousemove', (event) => {
      if (state.tool === 'rectangle') {
        updateRectangleDrawing();
        updateCanvasCursor(event.target);
        return;
      }

      if (state.tool === 'arrow') {
        updateArrowDrawing();
        updateCanvasCursor(event.target);
        return;
      }

      if (state.tool === 'freehand') {
        updateFreehandDrawing();
      }

      updateCanvasCursor(event.target);
    });

    state.stage.on('mouseleave', () => {
      updateCanvasCursor();
    });

    state.stage.on('mouseup', () => {
      if (state.tool === 'rectangle') {
        finishRectangleDrawing();
        return;
      }

      if (state.tool === 'arrow') {
        finishArrowDrawing();
        return;
      }

      if (state.tool === 'freehand') {
        finishFreehandDrawing();
      }
    });

    state.drawLayer.on('dragend', (event) => {
      if (!isDrawLayerNode(event.target)) return;
      saveHistorySnapshot();
      selectNode(event.target);
      updateCanvasCursor(event.target);
    });

    state.drawLayer.on('transformend', (event) => {
      if (!isDrawLayerNode(event.target)) return;
      saveHistorySnapshot();
      selectNode(event.target);
      updateCanvasCursor(event.target);
    });

    state.drawLayer.on('dblclick dbltap', (event) => {
      if (state.tool !== 'text') return;

      const target = event.target;
      if (!isTextNode(target)) return;

      event.cancelBubble = true;
      clearSelection();
      startInlineTextEditing(target, {
        initialValue: target.text(),
        isNewNode: false,
      });
    });
  }

  function clearStageState() {
    cancelActiveTextEditing();

    state.stage = null;
    state.imageLayer = null;
    state.drawLayer = null;
    state.transformerLayer = null;
    state.transformer = null;
    state.imageNode = null;
    state.baseImageWidth = 0;
    state.baseImageHeight = 0;
    state.stageScale = 1;
    state.history = [];
    state.historyIndex = -1;
    state.drawingRect = null;
    state.isDrawingRectangle = false;
    state.rectangleStart = null;
    state.drawingArrow = null;
    state.isDrawingArrow = false;
    state.arrowStart = null;
    state.drawingFreehand = null;
    state.isDrawingFreehand = false;
    state.activeTextEditor = null;

    syncToolbarAvailability();
  }

  function destroyActiveStage() {
    state.canvasInitToken += 1;
    state.stage?.destroy();
    clearStageState();
  }

  function hideAnnotationWorkspace() {
    if (elements.annotationWorkspace) {
      elements.annotationWorkspace.classList.add('hidden');
    }

    if (IS_QA) {
      document.body.style.overflow = '';
      document.documentElement.style.overflow = '';
      elements.annotationWorkspace?.classList.remove('is-landscape-mode');
    }

    if (elements.annotationFileLabel) {
      elements.annotationFileLabel.textContent = '';
    }

    syncToolbarAvailability();
  }

  function closeAnnotationWorkspace() {
    cancelActiveTextEditing();
    destroyActiveStage();
    state.activeIndex = null;
    hideAnnotationWorkspace();
    resetToolSelectionState();
    syncToolbarAvailability();
  }
  async function initCanvas(record, { resetHistory = true } = {}) {
    try {
      cancelActiveTextEditing();

      state.stage?.destroy();
      state.stage = null;

      if (!record || !elements.annotationCanvasContainer) return;

      state.canvasInitToken += 1;
      const currentToken = state.canvasInitToken;

      elements.annotationCanvasContainer.innerHTML = '';

      if (elements.annotationWorkspace) {
        elements.annotationWorkspace.classList.remove('hidden');
      }

      if (IS_QA) {
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
      }

      syncToolbarAvailability();

      if (elements.annotationFileLabel) {
        elements.annotationFileLabel.textContent = record.file?.name || '';
      }

      const imgEl = new window.Image();

      imgEl.onload = async () => {
        if (state.canvasInitToken !== currentToken) return;

        const isMobile = window.innerWidth <= 640;
        const isLandscape = imgEl.width > imgEl.height;

        let scale, canvasW, canvasH;

        if (IS_QA && isMobile && isLandscape) {
          // ── Mobile Landscape: Fill height, scroll horizontally ──
          elements.annotationWorkspace?.classList.add('is-landscape-mode');
          
          const chromeH = getQAChromeHeight();
          // Full height minus chrome
          const availH = window.innerHeight - chromeH;
          scale = availH / imgEl.height;
          canvasH = availH;
          canvasW = Math.floor(imgEl.width * scale);
        } else {
          // ── Default: Fill width, scroll vertically ──
          elements.annotationWorkspace?.classList.remove('is-landscape-mode');

          let containerWidth = getCanvasContainerWidth();

          if (!containerWidth) {
            await new Promise((r) => requestAnimationFrame(r));
            if (state.canvasInitToken !== currentToken) return;
            containerWidth = getCanvasContainerWidth();
          }

          if (!containerWidth) return;

          scale = containerWidth / imgEl.width;
          canvasW = containerWidth;
          canvasH = Math.floor(imgEl.height * scale);
        }

        const stage = new Konva.Stage({
          container: elements.annotationCanvasContainer,
          width: canvasW,
          height: canvasH,
        });

        state.stage = stage;
        syncToolbarAvailability();

        const imageLayer = new Konva.Layer();
        const drawLayer = new Konva.Layer();
        const transformerLayer = new Konva.Layer();

        const transformer = new Konva.Transformer({
          rotateEnabled: false,
          keepRatio: false,
          enabledAnchors: [
            'top-left', 'top-center', 'top-right',
            'middle-left', 'middle-right',
            'bottom-left', 'bottom-center', 'bottom-right',
          ],
        });

        const imageNode = new Konva.Image({
          image: imgEl,
          x: 0,
          y: 0,
          scaleX: scale,
          scaleY: scale,
        });

        imageLayer.add(imageNode);
        transformerLayer.add(transformer);
        stage.add(imageLayer);
        stage.add(drawLayer);
        stage.add(transformerLayer);
        imageLayer.draw();

        state.imageLayer = imageLayer;
        state.drawLayer = drawLayer;
        state.transformerLayer = transformerLayer;
        state.transformer = transformer;
        state.imageNode = imageNode;
        state.baseImageWidth = imgEl.width;
        state.baseImageHeight = imgEl.height;
        state.stageScale = scale;

        bindCanvasInteractions();
        updateDrawLayerDraggableState();
        state.history = [];
        state.historyIndex = -1;
        saveHistorySnapshot();
        syncToolbarAvailability();
      };

      imgEl.onerror = () => {
        if (state.canvasInitToken !== currentToken) return;
        showError(
          t(
            'report_annotation_status_load_failed',
            {},
            'Failed to load image into annotation canvas.'
          )
        );
        closeAnnotationWorkspace();
      };

      imgEl.src = getPreviewUrl(record);
    } catch (e) {
      console.error('initCanvas error:', e);
      syncToolbarAvailability();
    }
  }

  let resizeTimer = null;

  function syncInputFiles() {
    if (typeof DataTransfer === 'undefined') return;

    const transfer = new DataTransfer();
    state.files.forEach((item) => transfer.items.add(item.annotatedFile || item.file));
    elements.input.files = transfer.files;
  }

  function saveAnnotation() {
    if (!state.stage || state.activeIndex === null) return;

    finishActiveTextEditing({ commit: true });

    state.stage.toBlob({
      mimeType: 'image/png',
      quality: 1,
      callback: (blob) => {
        if (!blob) {
          showStatus(
            t(
              'report_annotation_status_save_failed',
              {},
              'Failed to save annotation. Please try again.'
            ),
            'danger'
          );
          return;
        }

        const record = state.files[state.activeIndex];
        if (!record?.file) {
          showStatus(
            t(
              'report_annotation_status_save_failed',
              {},
              'Failed to save annotation. Please try again.'
            ),
            'danger'
          );
          return;
        }

        const baseName = record.file.name.replace(/\.[^/.]+$/, '');
        const annotatedFile = new File([blob], `${baseName}-annotated.png`, {
          type: 'image/png',
          lastModified: Date.now(),
        });

        record.annotatedBlob = blob;
        record.annotatedFile = annotatedFile;
        record.hasAnnotation = true;

        // Paksa refresh URL preview agar memuat versi anotasi terbaru
        refreshPreviewUrl(record.id);

        if (typeof DataTransfer !== 'undefined') {
          const dt = new DataTransfer();
          state.files.forEach((r) => dt.items.add(r.annotatedFile || r.file));
          if (elements.input) elements.input.files = dt.files;
        }

        renderPreviewList();
        showStatus(
          t(
            'report_annotation_status_saved',
            {},
            'Annotation saved. The annotated image will be submitted with the form.'
          ),
          'success'
        );
      },
    });
  }

  function updateAddButtonVisibility() {
    if (!elements.addButton) return;

    if (state.files.length > 0 && state.files.length < MAX_FILES) {
      elements.addButton.classList.remove('hidden');
    } else {
      elements.addButton.classList.add('hidden');
    }
  }

  function updateActiveToolButton() {
    if (!elements.annotationToolButtons.length) return;

    elements.annotationToolButtons.forEach((button) => {
      const isActive = button.dataset.tool === state.tool;
      button.classList.toggle('is-active', isActive);
    });
  }

  function updateActiveColorButton() {
    if (!elements.annotationColorButtons.length) return;

    elements.annotationColorButtons.forEach((button) => {
      const isActive = button.dataset.color === state.color;
      button.classList.toggle('is-active', isActive);
    });
  }

  function bindToolbarEvents() {
    if (elements.annotationToolButtons.length) {
      elements.annotationToolButtons.forEach((button) => {
        button.addEventListener('click', () => {
          const selectedTool = button.dataset.tool;
          if (!selectedTool || state.tool === selectedTool) return;

          if (state.tool === 'rectangle' && state.isDrawingRectangle) {
            updateRectangleDrawing();
            finishRectangleDrawing();
          }

          if (state.tool === 'arrow' && state.isDrawingArrow) {
            updateArrowDrawing();
            finishArrowDrawing();
          }

          if (state.tool === 'freehand' && state.isDrawingFreehand) {
            finishFreehandDrawing();
          }

          if (state.tool === 'text' && selectedTool !== 'text') {
            cancelActiveTextEditing();
          }

          state.tool = selectedTool;
          updateActiveToolButton();
          updateDrawLayerDraggableState();
        });
      });
    }

    if (elements.annotationColorButtons.length) {
      elements.annotationColorButtons.forEach((button) => {
        button.addEventListener('click', () => {
          const selectedColor = button.dataset.color;
          if (!selectedColor || state.color === selectedColor) return;

          state.color = selectedColor;
          updateActiveColorButton();
        });
      });
    }
  }

  function renderPreviewList() {
    elements.previewList.innerHTML = '';

    const hasFiles = state.files.length > 0;

    elements.dropzone.classList.toggle('hidden', hasFiles);

    if (elements.previewWrapper) {
      elements.previewWrapper.classList.toggle('hidden', !hasFiles);
    }

    if (elements.emptyState) {
      elements.emptyState.classList.toggle('hidden', hasFiles);
    }

    if (!hasFiles) {
      closeAnnotationWorkspace();
      updateAddButtonVisibility();
      syncToolbarAvailability();
      return;
    }

    state.files.forEach((fileItem, index) => {
      const card = document.createElement('div');
      card.className = 'report-preview-list-item';

      const thumb = document.createElement('img');
      thumb.className = 'report-preview-list-thumb';
      thumb.src = getPreviewUrl(fileItem);
      thumb.alt = fileItem.file.name;

      const info = document.createElement('div');
      info.className = 'report-preview-list-info';

      const name = document.createElement('p');
      name.className = 'report-preview-list-name';
      name.textContent = fileItem.file.name;
      name.title = fileItem.file.name;

      const size = document.createElement('p');
      size.className = 'report-preview-list-size';
      size.textContent = formatFileSize(fileItem.file.size);

      info.append(name, size);

      if (fileItem.hasAnnotation) {
        const annotatedBadge = document.createElement('span');
        annotatedBadge.className = 'report-preview-pill';
        annotatedBadge.textContent = t('report_preview_badge_annotated', {}, 'Annotated');
        info.appendChild(annotatedBadge);
      }

      const actions = document.createElement('div');
      actions.className = 'report-preview-list-actions';

      const annotateBtn = document.createElement('button');
      annotateBtn.type = 'button';
      annotateBtn.className = 'report-preview-btn report-preview-btn-ghost report-preview-list-annotate-btn';
      annotateBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="m18 2 4 4" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 21.5 7 20l13-13a2.8 2.8 0 0 0-4-4L3 16l-1.5 5.5Z" />
        </svg>
        <span>${t('report_preview_button_annotate', {}, 'Annotate')}</span>
      `;
      annotateBtn.addEventListener('click', async () => {
        const record = state.files[index];
        if (!record) return;

        destroyActiveStage();
        state.activeIndex = index;
        await initCanvas(record);
        syncToolbarAvailability();
      });

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'report-preview-btn report-preview-btn-danger report-preview-list-remove-btn';
      removeBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
        </svg>
        <span>${t('report_preview_remove_label', {}, 'Remove')}</span>
      `;
      removeBtn.setAttribute(
        'aria-label',
        t('report_preview_remove_aria', { name: fileItem.file.name }, `Remove attachment ${fileItem.file.name}`)
      );
      removeBtn.addEventListener('click', () => {
        removeFileById(fileItem.id);
      });

      actions.append(annotateBtn, removeBtn);
      card.append(thumb, info, actions);
      elements.previewList.appendChild(card);
    });

    updateAddButtonVisibility();
    syncToolbarAvailability();
  }

  function removeFileById(fileId) {
    const index = state.files.findIndex((item) => item.id === fileId);
    if (index === -1) return;

    const removedActiveFile = state.activeIndex === index;

    if (state.activeIndex !== null) {
      if (state.activeIndex > index) {
        state.activeIndex -= 1;
      }
    }

    const [removed] = state.files.splice(index, 1);
    if (removed) revokePreviewUrl(removed.id);

    if (removedActiveFile) {
      closeAnnotationWorkspace();
    }

    clearError();
    syncInputFiles();
    renderPreviewList();
    syncToolbarAvailability();
  }

  function normalizeIncomingFiles(fileList) {
    const incomingFiles = Array.from(fileList || []);

    const invalidFiles = incomingFiles.filter((file) => !ACCEPTED_TYPES.includes(file.type));
    const validFiles = incomingFiles.filter((file) => ACCEPTED_TYPES.includes(file.type));

    const availableSlots = Math.max(MAX_FILES - state.files.length, 0);
    const filesToAdd = validFiles.slice(0, availableSlots);

    const errors = [];

    if (invalidFiles.length > 0) {
      errors.push(getImagesOnlyError());
    }

    if (validFiles.length > availableSlots) {
      errors.push(getMaxAttachmentsError());
    }

    return { filesToAdd, errors };
  }

  function appendFiles(fileList) {
    const { filesToAdd, errors } = normalizeIncomingFiles(fileList);

    if (errors.length > 0) {
      showError(errors.join(' '));
    } else {
      clearError();
    }

    if (filesToAdd.length === 0) {
      renderPreviewList();
      return;
    }

    filesToAdd.forEach((file) => {
      state.files.push({
        id: createId(),
        file,
        annotatedBlob: null,
        annotatedFile: null,
        hasAnnotation: false,
      });
    });

    syncInputFiles();
    renderPreviewList();

    if (hasAttachmentFiles()) {
      clearAttachmentRequiredError();
    }
  }

  function openFilePicker() {
    if (state.files.length >= MAX_FILES) {
      showError(getMaxAttachmentsError());
      updateAddButtonVisibility();
      return;
    }

    elements.input.click();
  }

  function isActionButtonTarget(target) {
    return Boolean(target.closest('button'));
  }

  function hasFilePayload(event) {
    const types = event.dataTransfer?.types;
    return Boolean(types && Array.from(types).includes('Files'));
  }

  elements.input.addEventListener('change', (event) => {
    appendFiles(event.target.files);
  });

  elements.dropzone.addEventListener('click', (event) => {
    if (isActionButtonTarget(event.target)) return;
    openFilePicker();
  });

  elements.dropzone.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' && event.key !== ' ') return;
    event.preventDefault();
    openFilePicker();
  });

  ['dragenter', 'dragover'].forEach((eventName) => {
    elements.dropzone.addEventListener(eventName, (event) => {
      event.preventDefault();
      event.stopPropagation();

      if (eventName === 'dragenter') {
        dropzoneDragDepth += 1;
      }

      setDropzoneDraggingState(true);
    });
  });

  ['dragleave', 'drop'].forEach((eventName) => {
    elements.dropzone.addEventListener(eventName, (event) => {
      event.preventDefault();
      event.stopPropagation();

      if (eventName === 'dragleave') {
        dropzoneDragDepth = Math.max(0, dropzoneDragDepth - 1);
      } else {
        dropzoneDragDepth = 0;
      }

      if (dropzoneDragDepth === 0) {
        setDropzoneDraggingState(false);
      }
    });
  });

  elements.dropzone.addEventListener('drop', (event) => {
    setDropzoneDraggingState(false);
    dropzoneDragDepth = 0;
    appendFiles(event.dataTransfer?.files);
  });

  ['dragenter', 'dragover'].forEach((eventName) => {
    window.addEventListener(eventName, (event) => {
      if (!hasFilePayload(event)) return;

      event.preventDefault();
    });
  });

  window.addEventListener('drop', (event) => {
    if (!hasFilePayload(event)) return;

    event.preventDefault();
    setDropzoneDraggingState(false);
    dropzoneDragDepth = 0;

    if (state.files.length >= MAX_FILES) {
      showError(getMaxAttachmentsError());
      updateAddButtonVisibility();
      return;
    }

    appendFiles(event.dataTransfer?.files);
  });

  if (elements.addButton) {
    elements.addButton.className = 'report-preview-btn report-preview-btn-ghost report-attachment-add-btn';
    elements.addButton.addEventListener('click', (event) => {
      event.preventDefault();
      openFilePicker();
    });
  }

  if (elements.annotationClose) {
    elements.annotationClose.addEventListener('click', (event) => {
      event.preventDefault();
      closeAnnotationWorkspace();
    });
  }

  if (elements.annotationUndo) {
    elements.annotationUndo.addEventListener('click', (event) => {
      event.preventDefault();
      undoHistory();
    });
  }

  if (elements.annotationRedo) {
    elements.annotationRedo.addEventListener('click', (event) => {
      event.preventDefault();
      redoHistory();
    });
  }

  if (elements.annotationDelete) {
    elements.annotationDelete.addEventListener('click', (event) => {
      event.preventDefault();
      deleteSelectedNodes();
    });
  }

  if (elements.annotationClearAll) {
    elements.annotationClearAll.addEventListener('click', (event) => {
      event.preventDefault();
      clearAllDrawings();
    });
  }

  if (elements.annotationSave) {
    elements.annotationSave.addEventListener('click', (event) => {
      event.preventDefault();
      saveAnnotation();
    });
  }

  // ─────────────────────────────────────────────
// Unified Frontend Validation
// ─────────────────────────────────────────────

let hasAttemptedSubmit = false;

function isValidEmailAddress(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(String(value || '').trim());
}

function setInputErrorState(input, isError) {
  if (!input) return;

  input.classList.toggle('report-field-input-error', isError);
  input.classList.toggle('report-field-input-normal', !isError);
  input.setAttribute('aria-invalid', isError ? 'true' : 'false');
}

function showFieldError(errorId, message) {
  const errorEl = document.getElementById(errorId);
  if (!errorEl) return;

  errorEl.textContent = message;
  errorEl.classList.remove('hidden');
}

function clearFieldError(errorId) {
  const errorEl = document.getElementById(errorId);
  if (!errorEl) return;

  errorEl.textContent = '';
  errorEl.classList.add('hidden');
}

function resolveFieldLabel(element, fallback = '') {
  const label = String(element?.dataset?.fieldLabel || '').trim();
  return label || fallback || 'This field';
}

function getSelectHiddenInput(wrapper) {
  const fieldName = String(wrapper?.dataset?.fieldName || '').trim();
  if (!fieldName) return null;

  return (
    wrapper.querySelector(`input[name="${fieldName}"]`) ||
    wrapper.querySelector(`input[name="${fieldName}[]"]`)
  );
}

function validateTextField(input) {
  if (!input || input.type === 'file') return true;

  const value = input.value.trim();
  const errorId = input.dataset.errorId;
  const fieldLabel = resolveFieldLabel(input, input.name);

  if (!value) {
    setInputErrorState(input, true);

    const message = t(
      'report_validation_required',
      { field: fieldLabel },
      `Please fill in ${fieldLabel}.`
    );

    showFieldError(errorId, message);
    return false;
  }

  if (input.type === 'email' && !isValidEmailAddress(value)) {
    setInputErrorState(input, true);

    const message = t(
      'report_validation_invalid_email',
      {},
      'Please enter a valid email address.'
    );

    showFieldError(errorId, message);
    return false;
  }

  setInputErrorState(input, false);
  clearFieldError(errorId);
  return true;
}

function validateSelectField(wrapper) {
  if (!wrapper) return true;

  const hiddenInput = getSelectHiddenInput(wrapper);
  const value = hiddenInput ? hiddenInput.value.trim() : '';
  const errorId = wrapper.dataset.errorId;
  const fieldLabel = resolveFieldLabel(wrapper, wrapper.dataset.fieldName || 'option');

  if (!value) {
    wrapper.classList.add('report-select-wrapper-error');

    const message = t(
      'report_validation_required',
      { field: fieldLabel },
      `Please fill in ${fieldLabel}.`
    );

    showFieldError(errorId, message);
    return false;
  }

  wrapper.classList.remove('report-select-wrapper-error');
  clearFieldError(errorId);
  return true;
}

function validateAttachmentField({ focusOnError = false } = {}) {
  if (hasAttachmentFiles()) {
    clearAttachmentRequiredError();
    return true;
  }

  setDropzoneErrorState(true);
  setAttachmentInputErrorState(true);

  const message = t(
    'report_validation_attachment_required',
    {},
    fallbackByLang({
      en: 'Attach at least 1 image before submitting report.',
      id: 'Lampirkan minimal 1 gambar sebelum mengirim laporan.',
    })
  );

  showError(message);

  if (elements.errorText) {
    elements.errorText.dataset.clientErrorType = 'attachment-required';
  }

  if (focusOnError) {
    elements.dropzone?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    elements.dropzone?.focus({ preventScroll: true });
  }

  return false;
}

function validateAllFields({ focusOnError = true } = {}) {
  let hasError = false;
  let firstErrorElement = null;

  const requiredInputs = elements.form.querySelectorAll('[data-required="true"]');
  requiredInputs.forEach((input) => {
    if (input.type === 'file') return;

    const isValid = validateTextField(input);
    if (!isValid) {
      hasError = true;
      if (!firstErrorElement) firstErrorElement = input;
    }
  });

  const requiredSelects = elements.form.querySelectorAll('[data-required-select="true"]');
  requiredSelects.forEach((wrapper) => {
    const isValid = validateSelectField(wrapper);
    if (!isValid) {
      hasError = true;
      if (!firstErrorElement) firstErrorElement = wrapper;
    }
  });

  const isAttachmentValid = validateAttachmentField({ focusOnError: false });
  if (!isAttachmentValid) {
    hasError = true;
    if (!firstErrorElement) firstErrorElement = elements.dropzone;
  }

  if (hasError && focusOnError && firstErrorElement) {
    firstErrorElement.scrollIntoView({
      behavior: 'smooth',
      block: 'center',
    });

    if (typeof firstErrorElement.focus === 'function') {
      firstErrorElement.focus({ preventScroll: true });
    }
  }

  return !hasError;
}

function bindLiveValidationEvents() {
  if (!elements.form) return;

  elements.form.querySelectorAll('[data-required="true"]').forEach((input) => {
    if (input.type === 'file') return;

    const handler = () => {
      if (hasAttemptedSubmit) {
        // After first submit: full re-validation so errors update live
        validateTextField(input);
      } else {
        // Before submit: only clear errors when input becomes valid
        const value = input.value.trim();
        const isCurrentlyError = input.classList.contains('report-field-input-error');

        if (isCurrentlyError && value) {
          // Valid email check for email fields
          if (input.type === 'email' && !isValidEmailAddress(value)) return;

          setInputErrorState(input, false);
          clearFieldError(input.dataset.errorId);
        }
      }
    };

    input.addEventListener('input', handler);
    input.addEventListener('blur', handler);
  });

  elements.form.querySelectorAll('[data-required-select="true"]').forEach((wrapper) => {
    const hiddenInput = getSelectHiddenInput(wrapper);

    const handler = () => {
      window.requestAnimationFrame(() => {
        if (hasAttemptedSubmit) {
          validateSelectField(wrapper);
        } else {
          // Before submit: only clear errors when a value is selected
          const value = hiddenInput ? hiddenInput.value.trim() : '';
          const isCurrentlyError = wrapper.classList.contains('report-select-wrapper-error');

          if (isCurrentlyError && value) {
            wrapper.classList.remove('report-select-wrapper-error');
            clearFieldError(wrapper.dataset.errorId);
          }
        }
      });
    };

    if (hiddenInput) {
      hiddenInput.addEventListener('change', handler);
      hiddenInput.addEventListener('input', handler);
    }

    wrapper.addEventListener('click', handler);
    wrapper.addEventListener('keydown', handler);
  });

  if (elements.input) {
    elements.input.addEventListener('change', () => {
      validateAttachmentField({ focusOnError: false });
    });
  }
}

  function init() {
    bindToolbarEvents();
    updateActiveToolButton();
    updateActiveColorButton();
    syncToolbarAvailability();
    bindLiveValidationEvents();

    if (elements.form && !IS_QA) {
      elements.form.addEventListener('submit', (event) => {
        hasAttemptedSubmit = true;
        const isValid = validateAllFields({ focusOnError: true });

        if (!isValid) {
          event.preventDefault();
        }
      });
    }

    window.addEventListener('keydown', handleCanvasKeyboardShortcuts);

    // QA fullscreen overlay: ESC menutup workspace
    if (IS_QA) {
      window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && elements.annotationWorkspace && !elements.annotationWorkspace.classList.contains('hidden')) {
          event.preventDefault();
          closeAnnotationWorkspace();
        }
      });
    }

    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        if (!state.stage || !state.baseImageWidth || !state.baseImageHeight) return;

        const isMobile = window.innerWidth <= 640;
        const isLandscape = state.baseImageWidth > state.baseImageHeight;

        let scale, canvasW, canvasH;

        if (IS_QA && isMobile && isLandscape) {
          elements.annotationWorkspace?.classList.add('is-landscape-mode');
          const chromeH = getQAChromeHeight();
          const availH = window.innerHeight - chromeH;
          scale = availH / state.baseImageHeight;
          canvasH = availH;
          canvasW = Math.floor(state.baseImageWidth * scale);
        } else {
          elements.annotationWorkspace?.classList.remove('is-landscape-mode');
          const container = elements.annotationCanvasContainer;
          if (!container) return;

          const containerWidth = Math.floor(container.offsetWidth);
          if (!containerWidth) return;

          scale = containerWidth / state.baseImageWidth;
          canvasW = containerWidth;
          canvasH = Math.floor(state.baseImageHeight * scale);
        }

        state.stageScale = scale;
        state.stage.width(canvasW);
        state.stage.height(canvasH);

        if (state.imageNode) {
          state.imageNode.scaleX(scale);
          state.imageNode.scaleY(scale);
        }

        state.stage.batchDraw();
      }, 200);
    });
    
    renderPreviewList();

    window.addEventListener('beforeunload', () => {
      destroyActiveStage();
      state.files.forEach((item) => revokePreviewUrl(item.id));
    });
  }

  init();
})();
