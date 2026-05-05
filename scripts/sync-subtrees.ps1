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

function Remove-GitBranchIfExists {
  param([string]$Name)
  $prev = $ErrorActionPreference
  $ErrorActionPreference = 'SilentlyContinue'
  git branch -D $Name 2>&1 | Out-Null
  $ErrorActionPreference = $prev
}

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
  Write-Host '>>> Splitting front/ -> tashyik-frontend (Nuxt at repo root)...' -ForegroundColor Cyan
  Remove-GitBranchIfExists $tmpFront
  git subtree split --prefix=front -b $tmpFront
  if ($LASTEXITCODE -ne 0) { throw "git subtree split --prefix=front failed (exit $LASTEXITCODE)" }
  if ($StrictPush) {
    git push frontend "${tmpFront}:main"
  } else {
    git push frontend "${tmpFront}:main" --force-with-lease
  }
  if ($LASTEXITCODE -ne 0) { throw "git push frontend failed (exit $LASTEXITCODE)" }

  Write-Host '>>> Splitting backend/ -> tashyik-backend (Laravel at repo root)...' -ForegroundColor Cyan
  Remove-GitBranchIfExists $tmpBack
  git subtree split --prefix=backend -b $tmpBack
  if ($LASTEXITCODE -ne 0) { throw "git subtree split --prefix=backend failed (exit $LASTEXITCODE)" }
  if ($StrictPush) {
    git push backend "${tmpBack}:main"
  } else {
    git push backend "${tmpBack}:main" --force-with-lease
  }
  if ($LASTEXITCODE -ne 0) { throw "git push backend failed (exit $LASTEXITCODE)" }
}
finally {
  Remove-GitBranchIfExists $tmpFront
  Remove-GitBranchIfExists $tmpBack
}

Write-Host '>>> Done. Frontend/backend repos mirror front/ and backend/ at repository root.' -ForegroundColor Green
