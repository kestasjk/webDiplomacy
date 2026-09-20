
export interface ViewedPhaseState {
  // The index of the phase currently being viewed, in the gameState.phases array.
  viewedPhaseIdx: number;
  // The index of the latest phase that the user has viewed.
  latestPhaseViewed: number;
}

export default ViewedPhaseState;
