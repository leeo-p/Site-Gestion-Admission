{
  description = "A very basic flake";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
    flake-utils = {
      url = "github:numtide/flake-utils";
      inputs.nixpkgs.follows = "nixpkgs";
    };
  };

  outputs = { self, nixpkgs, flake-utils }:
    flake-utils.lib.eachDefaultSystem (system: let
      pkgs = import nixpkgs {
        inherit system;
      };
    in {
      devShell = pkgs.mkShell {
        nativeBuildInputs = with pkgs; [
          (php74.buildEnv {
            extraConfig = ''
              display_errors=0
              display_startup_errors=0
              html_errors=0
              log_errors=1
              ignore_repeated_errors=0
              ignore_repeated_source=0
              report_memleaks=1
              track_errors=1
            '';
          })
        ];
      };
    });
}
