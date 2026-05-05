<#
.SYNOPSIS
  Push monorepo main to origin, then mirror front/ and backend/ to standalone repos.
  Uses git subtree split so tashyik-frontend and tashyik-backend get FILES AT REPO ROOT
  (no outer front/ or backend/ folder on GitHub).

.DESCRIPTION
  Remotes expected (git remote -v):
    origin     → https://github.com/devmohamed63/tashyik-freelancer.git
    frontend   → https://github.com/devmohamed63/tashyik-frontend.git
    backend    → https://github.com/devmohamed63/tashyik-backend.git

  Workflow:
    1) Optional: commit local changes on main
    2) git push origin main
    3) subtree split --prefix=front  → push to frontend main
    4) subtree split --prefix=backend → push to backend main

.PARAMETER SkipOrigin
  Skip pushing tashyik-freelancer (only update frontend/backend remotes).

.PARAMETER StrictPush
  Do not use --force-with-lease on frontend/backend (will fail if remote main advanced elsewhere).

.EXAMPLE
  .\scripts\sync-subtrees.ps1
#>

param(
  [switch] $SkipOrigin,
  [switch] $StrictPush
)

$ErrorActionPreference = 'Stop'
Set-Location (Resolve-Path (Join-Path $PSScriptRoot '..'))

function Test-GitRemote {
  param([string]$Name)
  $r = git remote 2>$null
  if ($r -notcontains $Name) {
    throw "Missing git remote '$Name'. Add with: git remote add $Name <url>"
  }
}

foreach ($remote in @('origin', 'frontend', 'backend')) {
  Test-GitRemote -Name $remote
}

$branch = git branch --show-current
if ($branch -ne 'main') {
  Write-Warning "Current branch is '$branch', not main."
}

if (-not $SkipOrigin) {
  Write-Host '>>> Pushing origin (tashyik-freelancer)...' -ForegroundColor Cyan
  git push origin main
}

$tmpFront = '_subtree_front'
$tmpBack = '_subtree_backend'

try {
  Write-Host '>>> Splitting front/ → tashyik-frontend (root = Nuxt app)...' -ForegroundColor Cyan
  git branch -D $tmpFront 2>$null | Out-Null
  git subtree split --prefix=front -b $tmpFront
  if ($StrictPush) {
    git push frontend "${tmpFront}:main"
  } else {
    git push frontend "${tmpFront}:main" --force-with-lease
  }

  Write-Host '>>> Splitting backend/ → tashyik-backend (root = Laravel)...' -ForegroundColor Cyan
  git branch -D $tmpBack 2>$null | Out-Null
  git subtree split --prefix=backend -b $tmpBack
  if ($StrictPush) {
    git push backend "${tmpBack}:main"
  } else {
    git push backend "${tmpBack}:main" --force-with-lease
  }
}
finally {
  git branch -D $tmpFront 2>$null | Out-Null
  git branch -D $tmpBack 2>$null | Out-Null
}

Write-Host '>>> Done. Frontend/backend repos mirror front/ and backend/ at repository root.' -ForegroundColor Green
