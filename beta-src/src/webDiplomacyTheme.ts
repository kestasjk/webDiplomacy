import { createTheme } from "@mui/material/styles";

declare module "@mui/material/styles" {
  interface BreakpointOverrides {
    xs: false;
    sm: false;
    md: false;
    lg: false;
    xl: false;
    mobile: true;
    mobileLg: true;
    tablet: true;
    desktop: true;
  }

  interface TypographyVariants {
    smallLabel: React.CSSProperties;
  }

  export interface TypographyVariantsOptions {
    smallLabel?: React.CSSProperties;
  }
}

declare module "@mui/material/Typography" {
  interface TypographyPropsVariantOverrides {
    smallLabel: true;
  }
}

const webDiplomacyTheme = createTheme({
  breakpoints: {
    values: {
      mobile: 0,
      mobileLg: 414,
      tablet: 834,
      desktop: 1500,
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
    body1: {
      fontSize: 14,
      lineHeight: 1.29,
    },
    h1: {
      fontSize: 20,
      fontWeight: 700,
      lineHeight: 1.2,
    },
    h2: {
      fontSize: 16,
      fontWeight: 700,
      lineHeight: 1.125,
    },
    h3: {
      fontSize: 14,
      fontWeight: 700,
      lineHeight: 1.15,
    },
    smallLabel: {
      fontSize: 10,
      fontWeight: 400,
      lineHeight: 1,
    },
  },
});

export default webDiplomacyTheme;
