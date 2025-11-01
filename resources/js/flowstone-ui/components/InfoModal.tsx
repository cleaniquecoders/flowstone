import React from 'react';

interface InfoModalProps {
  isOpen: boolean;
  onClose: () => void;
  workflowType: string;
}

export function InfoModal({ isOpen, onClose }: InfoModalProps) {
  if (!isOpen) return null;

  return (
    <div
      className="fixed inset-0 z-100 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      onClick={onClose}
    >
      <div
        className="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 overflow-hidden transform transition-all max-h-[90vh] overflow-y-auto"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div className="bg-linear-to-r from-flowstone-500 to-purple-600 px-6 py-5">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 className="text-xl font-bold text-white">Workflow & State Machine Guide</h3>
                <p className="text-sm text-white/80">Understanding the difference between workflow types</p>
              </div>
            </div>
            <button
              type="button"
              onClick={onClose}
              className="text-white/80 hover:text-white transition-colors"
              title="Close modal"
              aria-label="Close info modal"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        {/* Body */}
        <div className="p-8">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Workflow Info Card */}
            <div className="bg-linear-to-br from-blue-50 to-blue-100/50 rounded-xl border-2 border-blue-200 p-6 hover:shadow-lg transition-shadow">
              <div className="flex items-start gap-4">
                <div className="w-14 h-14 bg-blue-500 rounded-full flex items-center justify-center shrink-0 shadow-lg">
                  <svg className="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div className="flex-1 min-w-0">
                  <h3 className="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <span>Workflow</span>
                    <span className="px-2 py-0.5 bg-blue-500 text-white text-xs rounded-full font-semibold">Multiple States</span>
                  </h3>
                  <p className="text-sm text-gray-700 leading-relaxed mb-4">
                    A workflow models a process where objects can be in <strong className="text-blue-700">multiple places simultaneously</strong>.
                    Perfect for complex processes like document approval where multiple reviewers work in parallel.
                  </p>

                  <div className="space-y-3">
                    <div className="flex items-start gap-2">
                      <svg className="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      <span className="text-sm text-gray-700">Parallel execution of multiple states</span>
                    </div>
                    <div className="flex items-start gap-2">
                      <svg className="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      <span className="text-sm text-gray-700">Complex approval processes</span>
                    </div>
                    <div className="flex items-start gap-2">
                      <svg className="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      <span className="text-sm text-gray-700">Multi-department workflows</span>
                    </div>
                  </div>

                  <div className="mt-4 p-3 bg-white/70 rounded-lg border border-blue-300">
                    <p className="text-xs font-semibold text-blue-800 mb-1">Example Use Cases:</p>
                    <ul className="text-xs text-gray-600 space-y-1">
                      <li>• Document approval with multiple reviewers</li>
                      <li>• Multi-stage product development</li>
                      <li>• Concurrent task management</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            {/* State Machine Info Card */}
            <div className="bg-linear-to-br from-purple-50 to-purple-100/50 rounded-xl border-2 border-purple-200 p-6 hover:shadow-lg transition-shadow">
              <div className="flex items-start gap-4">
                <div className="w-14 h-14 bg-purple-500 rounded-full flex items-center justify-center shrink-0 shadow-lg">
                  <svg className="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <div className="flex-1 min-w-0">
                  <h3 className="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <span>State Machine</span>
                    <span className="px-2 py-0.5 bg-purple-500 text-white text-xs rounded-full font-semibold">Single State</span>
                  </h3>
                  <p className="text-sm text-gray-700 leading-relaxed mb-4">
                    A state machine holds a <strong className="text-purple-700">single state at a time</strong>.
                    Ideal for linear processes like order status (pending → processing → completed) or blog post publishing.
                  </p>

                  <div className="space-y-3">
                    <div className="flex items-start gap-2">
                      <svg className="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      <span className="text-sm text-gray-700">Sequential state transitions</span>
                    </div>
                    <div className="flex items-start gap-2">
                      <svg className="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      <span className="text-sm text-gray-700">Linear process flows</span>
                    </div>
                    <div className="flex items-start gap-2">
                      <svg className="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      <span className="text-sm text-gray-700">Simple status tracking</span>
                    </div>
                  </div>

                  <div className="mt-4 p-3 bg-white/70 rounded-lg border border-purple-300">
                    <p className="text-xs font-semibold text-purple-800 mb-1">Example Use Cases:</p>
                    <ul className="text-xs text-gray-600 space-y-1">
                      <li>• E-commerce order processing</li>
                      <li>• Blog post publishing workflow</li>
                      <li>• Simple approval processes</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Quick Tips Section */}
          <div className="mt-8 bg-linear-to-r from-flowstone-50 to-purple-50 rounded-xl border-2 border-flowstone-200 p-6">
            <div className="flex items-start gap-3 mb-4">
              <svg className="w-6 h-6 text-flowstone-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
              <div className="flex-1">
                <h4 className="font-bold text-gray-900 mb-3">Quick Design Tips</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div className="flex items-start gap-2">
                    <span className="text-flowstone-600 font-bold">🎯</span>
                    <span className="text-sm text-gray-700">Double-click nodes to edit labels</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <span className="text-flowstone-600 font-bold">🔗</span>
                    <span className="text-sm text-gray-700">Drag from handles to create connections</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <span className="text-flowstone-600 font-bold">🗑️</span>
                    <span className="text-sm text-gray-700">Select and press Delete to remove</span>
                  </div>
                  <div className="flex items-start gap-2">
                    <span className="text-flowstone-600 font-bold">💾</span>
                    <span className="text-sm text-gray-700">Click Save to persist your changes</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Footer */}
        <div className="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <button
            onClick={onClose}
            className="cursor-pointer w-full px-6 py-3 bg-linear-to-r from-flowstone-600 to-purple-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all"
          >
            Got it, let's design!
          </button>
        </div>
      </div>
    </div>
  );
}
