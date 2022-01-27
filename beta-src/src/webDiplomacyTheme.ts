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
    label: React.CSSProperties;
  }

  export interface TypographyVariantsOptions {
    label?: React.CSSProperties;
  }
}

declare module "@mui/material/Typography" {
  interface TypographyPropsVariantOverrides {
    label: true;
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
});

webDiplomacyTheme.typography.body1 = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 14,
  fontWeight: 400,
  lineHeight: 1.2,
};

webDiplomacyTheme.typography.h1 = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 16,
  fontWeight: 700,
  lineHeight: 1.2,
  [webDiplomacyTheme.breakpoints.up("desktop")]: {
    fontSize: 20,
  },
};

webDiplomacyTheme.typography.h2 = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 14,
  fontWeight: 700,
  lineHeight: 1.2,
};

webDiplomacyTheme.typography.label = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 10,
  fontWeight: 400,
  lineHeight: 1,
};

export default webDiplomacyTheme;
