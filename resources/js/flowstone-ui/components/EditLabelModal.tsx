import React, { useEffect, useRef, useState } from 'react';

interface EditLabelModalProps {
  isOpen: boolean;
  currentLabel: string;
  nodeType: 'place' | 'transition';
  onSave: (newLabel: string) => void;
  onCancel: () => void;
}

export function EditLabelModal({ isOpen, currentLabel, nodeType, onSave, onCancel }: EditLabelModalProps) {
  const [label, setLabel] = useState(currentLabel);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    setLabel(currentLabel);
    if (isOpen && inputRef.current) {
      // Focus and select text when modal opens
      setTimeout(() => {
        inputRef.current?.focus();
        inputRef.current?.select();
      }, 100);
    }
  }, [isOpen, currentLabel]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (label.trim()) {
      onSave(label.trim());
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Escape') {
      onCancel();
    }
  };

  if (!isOpen) return null;

  const colors = nodeType === 'place'
    ? { bg: 'bg-blue-50', border: 'border-blue-500', text: 'text-blue-700', button: 'bg-blue-600 hover:bg-blue-700' }
    : { bg: 'bg-orange-50', border: 'border-orange-500', text: 'text-orange-700', button: 'bg-orange-600 hover:bg-orange-700' };

  return (
    <div
      className="fixed inset-0 z-100 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      onClick={onCancel}
    >
      <div
        className="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform transition-all"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div className={`${colors.bg} ${colors.border} border-b-4 px-6 py-4`}>
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className={`w-10 h-10 rounded-full ${colors.button} flex items-center justify-center`}>
                <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </div>
              <div>
                <h3 className="text-lg font-bold text-gray-900">Edit {nodeType === 'place' ? 'Place' : 'Transition'} Label</h3>
                <p className="text-sm text-gray-600">Update the name of this node</p>
              </div>
            </div>
            <button
              type="button"
              onClick={onCancel}
              className="text-gray-400 hover:text-gray-600 transition-colors"
              title="Close modal"
              aria-label="Close modal"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        {/* Body */}
        <form onSubmit={handleSubmit} className="p-6">
          <div className="mb-6">
            <label htmlFor="label-input" className="block text-sm font-semibold text-gray-700 mb-2">
              Node Label
            </label>
            <input
              ref={inputRef}
              id="label-input"
              type="text"
              value={label}
              onChange={(e) => setLabel(e.target.value)}
              onKeyDown={handleKeyDown}
              className="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-gray-900 font-medium placeholder-gray-400"
              placeholder={`Enter ${nodeType} label...`}
              maxLength={100}
            />
            <div className="mt-2 flex items-center justify-between text-xs">
              <span className="text-gray-500">
                {nodeType === 'place' ? '🔵 Place names should be descriptive states' : '🔶 Transition names should be actions'}
              </span>
              <span className={`font-medium ${label.length > 80 ? 'text-orange-600' : 'text-gray-400'}`}>
                {label.length}/100
              </span>
            </div>
          </div>

          {/* Footer */}
          <div className="flex items-center gap-3">
            <button
              type="button"
              onClick={onCancel}
              className="flex-1 px-4 py-2.5 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={!label.trim()}
              className={`flex-1 px-4 py-2.5 ${colors.button} text-white font-semibold rounded-lg shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed`}
            >
              <span className="flex items-center justify-center gap-2">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
                Save Changes
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
