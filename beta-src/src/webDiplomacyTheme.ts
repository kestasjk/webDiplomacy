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
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          borderRadius: 18,
          padding: "10px 18px 12px",
        },
      },
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
    button: {
      fontSize: 12,
      fontWeight: 700,
      lineHeight: 1.2,
      textTransform: "none",
    },
  },
});

export default webDiplomacyTheme;
