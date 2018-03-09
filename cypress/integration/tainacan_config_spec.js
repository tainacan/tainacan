describe('Configuration Plugin Test', function () {

  beforeEach(() => {
    cy.loginByUI()
  })

  context ('Configuration', function(){
    it('plugins page', function(){
      cy.visit('wp-admin/plugins.php')
      cy.get('h1').should('contain', 'Plugins')
    })

    it('plugin active', function () {
      cy.contains('Tainacan').click()
      cy.get('h1').should('contain', 'Collections Page')
    })
  })
})
