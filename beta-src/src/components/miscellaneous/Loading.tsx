import React, { FC, ReactElement, useState, useEffect } from "react";
import { motion } from "framer-motion";
import dagger from "../../assets/png/dagger-pencil-01.png";
import pencil from "../../assets/png/dagger-pencil-02.png";
import seasonIcons from "../../assets/png/season-icons.png";

interface WDUIProps {
  show: boolean;
  onLoadingFinished: () => void;
  children: React.ReactNode;
}

interface WDLoadingBarProps {
  percentage: number;
}

const WDLoadingBar: FC<WDLoadingBarProps> = function ({
  percentage,
}: WDLoadingBarProps): ReactElement {
  return (
    <div className="h-[7px] w-[200px] bg-[#565656] rounded-full mx-auto">
      <div
        className="h-[7px] rounded-full bg-gradient-to-r from-[#323E34] via-[#C8B897] to-white"
        style={{ width: `${percentage}%` }}
      />
    </div>
  );
};

const variants = {
  showLayover: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.5,
    },
  },
  hideLayover: {
    y: -2000,
    transition: {
      duration: 1,
    },
  },
  showPencil: {
    opacity: 1,
    y: 0,
    x: 0,
    display: "block",
    transition: {},
  },
  showDagger: {
    opacity: 1,
    y: 0,
    x: 0,
    display: "block",
    transition: {},
  },
  showIcons: {
    opacity: 1,
    transition: {
      duration: 2,
    },
  },
  loadingUp: {
    opacity: 1,
    y: 0,
    display: "block",
    transition: {
      delay: 2,
      duration: 0.5,
    },
  },
};

const initialValues = {
  icons: false,
  daggerAndPencil: false,
  loadingContainer: false,
  loadingBar: false,
};

const WDLoading: FC<WDUIProps> = function ({
  show,
  onLoadingFinished,
  children,
}): ReactElement {
  const [percentage, setPercentage] = useState<number>(0);
  const [animationsSequence, setAnimationsSequence] = useState(initialValues);

  // eslint-disable-next-line consistent-return
  useEffect(() => {
    if (animationsSequence.loadingBar) {
      const interval = setInterval(() => {
        setPercentage((p) => p + 2);
      }, 350);
      if (percentage === 100) {
        setPercentage(0);
        setAnimationsSequence(initialValues);
        clearInterval(interval);
        onLoadingFinished();
      }
      return () => clearInterval(interval);
    }
  }, [animationsSequence.loadingBar, percentage]);

  return (
    <motion.div
      animate={show ? "showLayover" : "hideLayover"}
      variants={variants}
      style={{ y: -2000 }}
      onAnimationComplete={(definition) => {
        if (definition === "showLayover") {
          setAnimationsSequence({ ...animationsSequence, icons: true });
        }
      }}
      className="absolute top-0 w-full h-full loading bg-loading bg-contain z-[9999] flex flex-col justify-center select-none"
    >
      <div className="relative w-[70%] sm:w-[600px] mx-auto">
        <motion.img
          src={seasonIcons}
          alt="season icons"
          animate={animationsSequence.icons ? "showIcons" : ""}
          variants={variants}
          className="mx-auto opacity-0"
          onAnimationComplete={(definition) => {
            if (definition === "showIcons") {
              setAnimationsSequence({
                ...animationsSequence,
                daggerAndPencil: true,
              });
            }
          }}
        />
        <motion.img
          src={dagger}
          alt="diplomacy icon"
          animate={animationsSequence.daggerAndPencil ? "showDagger" : ""}
          style={{ x: "-100%", y: "-100%" }}
          variants={variants}
          className="absolute top-0 left-0 hidden"
        />
        <motion.img
          src={pencil}
          alt="diplomacy icon"
          animate={animationsSequence.daggerAndPencil ? "showPencil" : ""}
          style={{ x: "100%", y: "-100%" }}
          variants={variants}
          className="absolute top-0 left-0 hidden"
        />
      </div>
      <motion.div
        className="w-full text-white text-center uppercase text-lg tracking-[0.5rem] font-medium mt-20 sm:mt-26 opacity-0 whitespace-pre-line"
        animate={animationsSequence.icons ? "showIcons" : ""}
        variants={variants}
        onAnimationComplete={(definition) => {
          if (definition === "showIcons") {
            setAnimationsSequence({
              ...animationsSequence,
              loadingContainer: true,
            });
          }
        }}
      >
        {children}
      </motion.div>
      <motion.div
        className="text-white mt-20 opacity-0"
        animate={animationsSequence.icons ? "loadingUp" : ""}
        variants={variants}
        style={{ y: 1000 }}
        onAnimationComplete={() => {
          setAnimationsSequence({
            ...animationsSequence,
            loadingBar: true,
          });
        }}
      >
        <WDLoadingBar percentage={percentage} />
        <div className="uppercase pt-4 text-center tracking-[0.5rem]">
          Loading
        </div>
      </motion.div>
    </motion.div>
  );
};

export default WDLoading;
