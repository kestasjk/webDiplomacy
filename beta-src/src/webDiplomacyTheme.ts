import { createTheme } from "@mui/material/styles";

const webDiplomacyTheme = createTheme({
  breakpoints: {
    values: {
      xs: 0,
      sm: 414,
      md: 834,
      lg: 1200,
      xl: 1500,
    },
  },
  palette: {
    error: {
      main: "#f00",
      contrastText: "#fff",
    },
    primary: {
      main: "#000",
      contrastText: "#fff",
    },
    secondary: {
      main: "#fff",
      contrastText: "#000",
    },
  },
  typography: {
    fontFamily: "SF Pro Display, Segoe UI, Droid Sans, sans-serif",
  },
});

export default webDiplomacyTheme;
