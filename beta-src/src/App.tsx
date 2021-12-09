import * as React from "react";
import "./assets/css/App.css";
import map from "./assets/svg/map.svg";

const App: React.FC = function (): React.ReactElement {
  return (
    <div className="App">
      <img alt="Game Map" src={map} />
    </div>
  );
};

export default App;
